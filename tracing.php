<?php declare(strict_types=1);

use App\Application\OpenTelemetry\Messenger\Context\SymfonyMessengerPropagationGetterSetter;
use App\Application\Service\EventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use Elastica\Client;
use Elastica\Exception\ExceptionInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Endpoints\AbstractEndpoint;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Metrics\{
    GaugeInterface,
    CounterInterface
};
use OpenTelemetry\SemConv\TraceAttributeValues;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode,
    SpanInterface
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\{
    ArrayAccessGetterSetter,
    PropagationGetterInterface,
    PropagationSetterInterface
};
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\{
    HttpKernel,
    HttpKernelInterface
};
use Symfony\Contracts\HttpClient\{
    HttpClientInterface,
    ResponseInterface
};
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface,
    Stamp\TransportMessageIdStamp};
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use function OpenTelemetry\Instrumentation\hook;

if (extension_loaded('opentelemetry')) {
    final class RequestPropagationGetter implements PropagationGetterInterface
    {
        public static function instance(): self
        {
            static $instance;

            return $instance ??= new self();
        }

        public function keys($carrier): array
        {
            assert($carrier instanceof Request);

            return $carrier->headers->keys();
        }

        public function get($carrier, string $key): ?string
        {
            assert($carrier instanceof Request);

            return $carrier->headers->get($key);
        }
    }

    final class ResponsePropagationSetter implements PropagationSetterInterface
    {
        public static function instance(): self
        {
            static $instance;

            return $instance ??= new self();
        }

        public function keys($carrier): array
        {
            assert($carrier instanceof Response);

            return $carrier->headers->keys();
        }

        public function set(&$carrier, string $key, string $value): void
        {
            assert($carrier instanceof Response);

            $carrier->headers->set($key, $value);
        }
    }

    class ConsoleEnvPropagationGetterSetter implements PropagationGetterInterface, PropagationSetterInterface
    {
        private static ?self $instance = null;

        public static function instance(): self
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * @param mixed $carrier
         */
        public function keys($carrier): array
        {
            if (is_array($carrier) === false) {
                return [];
            }

            return $carrier;
        }

        /**
         * @param mixed $carrier
         */
        public function get($carrier, string $key): ?string
        {
            if (is_array($carrier) === false) {
                return null;
            }

            return $carrier[$key] ?? null;
        }

        /**
         * @param mixed $carrier
         */
        public function set(&$carrier, string $key, string $value): void
        {
            putenv(sprintf('%s=%s', $key, $value));
        }
    }

    final class Helper
    {
        public static function supportsProgress(string $class): bool
        {
            return $class !== 'ApiPlatform\Symfony\Bundle\Test\Client';
        }

        public static function counter(string $name, ?string $unit = null): CounterInterface
        {
            return Globals::meterProvider()
                ->getMeter(__CLASS__)
                ->createCounter($name, $unit);
        }

        public static function gauge(string $name, ?string $unit = null): GaugeInterface
        {
            return Globals::meterProvider()
                ->getMeter(__CLASS__)
                ->createGauge($name, $unit);
        }
    }

    ////////////////////////////Apcu////////////////////////////

    $apcuOperations = [
        'apcu_add',
        'apcu_cache_info',
        'apcu_cas',
        'apcu_clear_cache',
        'apcu_dec',
        'apcu_delete',
        'apcu_enabled',
        'apcu_entry',
        'apcu_exists',
        'apcu_fetch',
        'apcu_inc',
        'apcu_key_info',
        'apcu_sma_info',
        'apcu_store'
    ];

    foreach ($apcuOperations as $apcuOperation) {
        $counters = new stdClass;

        hook(
            null,
            $apcuOperation,
            pre: static function () use ($apcuOperation, $counters): void {
                $counter = $counters->{$apcuOperation} ?? null;

                if ($counter instanceof CounterInterface === false) {
                    $counter = Helper::counter($apcuOperation, 'calls');
                    $counters->{$apcuOperation} = $counter;
                }

                $counter->add(1);
            }
        );
    }

    ////////////////////////////HttpKernel////////////////////////////

    hook(
        HttpKernel::class,
        'handle',
        pre: static function (HttpKernel $kernel, array $params, string $class, string $function, ?string $filename, ?int $lineno): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            $request = $params[0] instanceof Request ? $params[0] : null;
            $type = $params[1] ?? HttpKernelInterface::MAIN_REQUEST;
            $method = $request?->getMethod() ?? 'unknown';
            $name = $type === HttpKernelInterface::SUB_REQUEST
                ? sprintf('%s %s', $method, $request?->attributes?->get('_controller') ?? 'sub-request')
                : $method;

            $builder = $instrumentation
                ->tracer()
                ->spanBuilder($name)
                ->setSpanKind(($type === HttpKernelInterface::SUB_REQUEST) ? SpanKind::KIND_INTERNAL : SpanKind::KIND_SERVER)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute('type', 'request');

            $parent = Context::getCurrent();
            if ($request) {
                $parent = Globals::propagator()->extract($request, RequestPropagationGetter::instance());
                $span = $builder
                    ->setParent($parent)
                    ->setAttribute(TraceAttributes::URL_FULL, $request->getUri())
                    ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $request->getMethod())
                    ->setAttribute(TraceAttributes::HTTP_REQUEST_BODY_SIZE, $request->headers->get('Content-Length'))
                    ->setAttribute(TraceAttributes::URL_SCHEME, $request->getScheme())
                    ->setAttribute(TraceAttributes::URL_PATH, $request->getPathInfo())
                    ->setAttribute(TraceAttributes::USER_AGENT_ORIGINAL, $request->headers->get('User-Agent'))
                    ->setAttribute(TraceAttributes::SERVER_ADDRESS, $request->getHost())
                    ->setAttribute(TraceAttributes::SERVER_PORT, $request->getPort())
                    ->startSpan();

                $request->attributes->set(SpanInterface::class, $span);
            } else {
                $span = $builder->startSpan();
            }
            Context::storage()->attach($span->storeInContext($parent));

            return [$request];
        },
        post: static function (HttpKernel $kernel, array $params, ?Response $response, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            $request = ($params[0] instanceof Request) ? $params[0] : null;
            if ($request !== null) {
                $routeName = $request->attributes->get('_route', '');

                if ($routeName !== '') {
                    $span
                        ->updateName(sprintf('%s %s', $request->getMethod(), $routeName))
                        ->setAttribute(TraceAttributes::HTTP_ROUTE, $routeName);
                }
            }

            if ($exception !== null) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);

                if ($response !== null && $response->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                }
            }

            if ($response === null) {
                $span->end();

                return;
            }

            if ($response->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                $span->setStatus(StatusCode::STATUS_ERROR);
            }

            $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $response->getStatusCode());
            $span->setAttribute(TraceAttributes::NETWORK_PROTOCOL_VERSION, $response->getProtocolVersion());
            $contentLength = $response->headers->get('Content-Length');

            if ($contentLength === null && is_string($response->getContent())) {
                $contentLength = strlen($response->getContent());
            }

            $span->setAttribute(TraceAttributes::HTTP_RESPONSE_BODY_SIZE, $contentLength);

            $span->end();
        }
    );

    hook(
        HttpKernel::class,
        'terminate',
        post: static function (): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
        }
    );

    hook(
        HttpKernel::class,
        'handleThrowable',
        pre: static function (HttpKernel $kernel, array $params): array {
            /** @var Throwable $throwable */
            $throwable = $params[0];

            Span::getCurrent()
                ->recordException($throwable, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);

            return $params;
        }
    );

    ////////////////////////////HttpClientInterface////////////////////////////

    hook(
        HttpClientInterface::class,
        'request',
        pre: static function (
            HttpClientInterface $client,
            array $params,
            string $class,
            string $function,
            ?string $filename,
            ?int $lineno,
        ): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            $builder = $instrumentation
                ->tracer()
                ->spanBuilder(sprintf('%s', $params[0]))
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttribute(TraceAttributes::PEER_SERVICE, parse_url((string) $params[1])['host'] ?? null)
                ->setAttribute(TraceAttributes::URL_FULL, (string) $params[1])
                ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $params[0])
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute('type', 'request');

            $propagator = Globals::propagator();
            $parent = Context::getCurrent();

            $span = $builder
                ->setParent($parent)
                ->startSpan();

            $requestOptions = $params[2] ?? [];

            if (!isset($requestOptions['headers'])) {
                $requestOptions['headers'] = [];
            }

            if (Helper::supportsProgress($class) === false) {
                $context = $span->storeInContext($parent);
                $propagator->inject($requestOptions['headers'], ArrayAccessGetterSetter::getInstance(), $context);

                Context::storage()->attach($context);

                return $params;
            }

            $previousOnProgress = $requestOptions['on_progress'] ?? null;

            $requestOptions['on_progress'] = static function (int $dlNow, int $dlSize, array $info) use ($previousOnProgress, $span): void {
                if ($previousOnProgress !== null) {
                    $previousOnProgress($dlNow, $dlSize, $info);
                }

                $statusCode = $info['http_code'];

                if ($statusCode !== 0 && $statusCode !== null && $span->isRecording()) {
                    $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $statusCode);

                    if ($statusCode >= 400 && $statusCode < 600) {
                        $span->setStatus(StatusCode::STATUS_ERROR);
                    }

                    $span->end();
                }
            };

            $context = $span->storeInContext($parent);
            $propagator->inject($requestOptions['headers'], ArrayAccessGetterSetter::getInstance(), $context);

            Context::storage()->attach($context);
            $params[2] = $requestOptions;

            return $params;
        },
        post: static function (HttpClientInterface $client, array $params, ?ResponseInterface $response, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                $span->end();

                return;
            }

            if ($response !== null && Helper::supportsProgress(get_class($client)) === false) {
                $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $response->getStatusCode());

                if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
                    $span->setStatus(StatusCode::STATUS_ERROR);
                }
            }
        }
    );

    ////////////////////////////cli console////////////////////////////

    hook(
        Command::class,
        'run',
        pre: static function (Command $command, array $params, string $class, string $function): void {
            $spanName = $command->getName();
            if ($spanName === 'messenger:consume') {
                return;
            }

            $instrumentation = new CachedInstrumentation(__FILE__);
            $parentContext = Globals::propagator()->extract(getenv(), ConsoleEnvPropagationGetterSetter::instance());

            if ($spanName === null || $spanName === '') {
                $spanName = $class . '::' . $function;
            }
            $spanName = sprintf('bin/console %s', $spanName);

            $span = $instrumentation->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_SERVER)
                ->setParent($parentContext)
                ->setAttributes([
                    TraceAttributes::CODE_FUNCTION => $function,
                    TraceAttributes::CODE_NAMESPACE => $class,
                    'type' => 'console-command',
                    'console.command.class' => $command::class
                ])
                ->addLink(Span::fromContext($parentContext)->getContext())
                ->startSpan();

            $context = $span->storeInContext(Context::getCurrent());

            Globals::propagator()->inject($ignoredVar, ConsoleEnvPropagationGetterSetter::instance(), $context);
            Context::storage()->attach($context);
        },
        post: static function (Command $command, array $params, $exitCode, ?Throwable $exception): void {
            if ($command->getName() === 'messenger:consume') {
                return;
            }

            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            $status = $exitCode !== 0 || $exception !== null ? StatusCode::STATUS_ERROR : StatusCode::STATUS_OK;

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
            }

            $exitCode = (int) $exitCode;
            $span->setAttribute('exit.code', $exitCode);
            $span->setStatus($status);
            $span->end();
        }
    );

    ////////////////////////////MessageBusInterface////////////////////////////

    hook(
        MessageBusInterface::class,
        'dispatch',
        pre: static function (
            MessageBusInterface $bus,
            array $params,
            string $class,
            string $function,
            ?string $filename,
            ?int $lineno,
        ): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            /** @var object|Envelope $message */
            $message = $params[0];
            $messageClass = get_class($message);

            $builder = $instrumentation->tracer()
                ->spanBuilder(sprintf('MESSENGER DISPATCH %s', $messageClass))
                ->setSpanKind(SpanKind::KIND_PRODUCER)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute('symfony.messenger.bus', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            $parent = Context::getCurrent();
            $span = $builder
                ->setParent($parent)
                ->startSpan();

            $context = $span->storeInContext($parent);
            Context::storage()->attach($context);

            return $params;
        },
        post: static function (MessageBusInterface $bus, array $params, ?Envelope $result, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true,
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        }
    );

    hook(
        SenderInterface::class,
        'send',
        pre: static function (
            SenderInterface $bus,
            array $params,
            string $class,
            string $function,
            ?string $filename,
            ?int $lineno,
        ): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            /** @var Envelope $envelope */
            $envelope = $params[0];
            $messageClass = get_class($envelope->getMessage());
            $transportMessageIdStamp = $envelope->last(TransportMessageIdStamp::class);

            $builder = $instrumentation
                ->tracer()
                ->spanBuilder(sprintf('MESSENGER SEND %s', $messageClass))
                ->setSpanKind(SpanKind::KIND_PRODUCER)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute(TraceAttributes::MESSAGING_SYSTEM, TraceAttributeValues::MESSAGING_SYSTEM_RABBITMQ)
                ->setAttribute(TraceAttributes::MESSAGING_OPERATION_TYPE, TraceAttributeValues::MESSAGING_OPERATION_TYPE_PUBLISH)
                ->setAttribute('symfony.messenger.transport', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
                $builder->setAttribute(TraceAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
            }

            $parent = Context::getCurrent();

            $span = $builder
                ->setParent($parent)
                ->startSpan();

            $context = $span->storeInContext($parent);

            Globals::propagator()->inject($envelope, SymfonyMessengerPropagationGetterSetter::instance(), $context);
            Context::storage()->attach($context);

            return $params;
        },
        post: static function (SenderInterface $sender, array $params, ?Envelope $result, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        }
    );

    ////////////////////////////PDO////////////////////////////

    class PDOInstrumentation
    {
        public static function register(): void
        {
            $instrumentation = new CachedInstrumentation(__CLASS__);
            $databaseParams = new stdClass;

            self::hookPDO($instrumentation, $databaseParams);
            self::hookPDOStatement($instrumentation, $databaseParams);
        }

        private static function hookPDO(CachedInstrumentation $instrumentation, stdClass $databaseParams): void
        {
            hook(
                PDO::class,
                '__construct',
                pre: static function (PDO $pdo, array $params) use ($databaseParams): void {
                    $databaseUrl = $params[0] ?? null;

                    if (is_string($databaseUrl)) {
                        preg_match('~dbname=([a-zA-Z0-9]+);~', $databaseUrl, $matches);
                        $databaseParams->dbname = $matches[1] ?? null;

                        preg_match('~host=([a-zA-Z0-9]+);~', $databaseUrl, $matches);
                        $databaseParams->host = $matches[1] ?? null;
                    }

                    $databaseParams->username = $params[1] ?? null;
                }
            );

            hook(
                PDO::class,
                'exec',
                pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    $sql = $params[0];
                    assert(is_string($sql));

                    self::startSpan($instrumentation, $sql, $class, $function, $databaseParams);
                },
                post: self::post()
            );

            hook(
                PDO::class,
                'query',
                pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    /* @var string $params */
                    $sql = $params[0];

                    self::startSpan($instrumentation, $sql, $class, $function, $databaseParams);
                },
                post: self::post()
            );

            hook(
                PDO::class,
                'beginTransaction',
                pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    self::startSpan($instrumentation, 'PDO->beginTransaction', $class, $function, $databaseParams);
                },
                post: self::post()
            );

            hook(
                PDO::class,
                'commit',
                pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    self::startSpan($instrumentation, 'PDO->commit', $class, $function, $databaseParams);
                },
                post: self::post()
            );

            hook(
                PDO::class,
                'rollBack',
                pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    self::startSpan($instrumentation, 'PDO->rollBack', $class, $function, $databaseParams);
                },
                post: self::post()
            );
        }

        private static function hookPDOStatement(CachedInstrumentation $instrumentation, stdClass $databaseParams): void
        {
            hook(
                PDOStatement::class,
                'execute',
                pre: static function (PDOStatement $statement, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                    self::startSpan($instrumentation, $statement->queryString, $class, $function, $databaseParams);
                },
                post: self::post()
            );
        }

        private static function startSpan(
            CachedInstrumentation $instrumentation,
            string $sql,
            string $class,
            string $function,
            stdClass $databaseParams,
        ): void {
            if ($sql === '') {
                $sql = 'Empty sql';
            }

            $spanName = self::simplifySql($sql);

            $spanBuilder = $instrumentation->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_INTERNAL)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_MYSQL)
                ->setAttribute(TraceAttributes::DB_QUERY_TEXT, $sql)
                ->setAttribute(TraceAttributes::DB_OPERATION_NAME, strtok($sql, ' '))
                ->setAttribute(TraceAttributes::SERVER_ADDRESS, $databaseParams->host)
                ->setAttribute(TraceAttributes::DB_NAMESPACE, $databaseParams->dbname)
                ->setAttribute('db.user', $databaseParams->username);

            $span = $spanBuilder->startSpan();

            $context = $span->storeInContext(Context::getCurrent());
            Context::storage()->attach($context);
        }

        private static function post(): Closure
        {
            return static function ($statement, $params, $result, ?Throwable $exception): void {
                $scope = Context::storage()->scope();
                if ($scope === null) {
                    return;
                }

                $scope->detach();
                $span = Span::fromContext($scope->context());

                if ($exception !== null) {
                    $span->recordException($exception, [
                        TraceAttributes::EXCEPTION_ESCAPED => true
                    ]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                } else {
                    $span->setStatus(StatusCode::STATUS_OK);
                }

                $span->end();
            };
        }

        private static function simplifySql(string $sql): string
        {
            $sql = preg_replace('~\s+~', ' ', trim($sql));

            if (preg_match('~^(SELECT).*?FROM\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
                return strtolower($matches[1]) . ' FROM ' . $matches[2];
            } elseif (preg_match('~^(UPDATE)\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
                return strtolower($matches[1]) . ' ' . $matches[2];
            } elseif (preg_match('~^(INSERT INTO)\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
                return 'INSERT ' . strtolower($matches[2]);
            }

            return $sql;
        }
    }

    PDOInstrumentation::register();

    ////////////////////////////Elasticsearch////////////////////////////

    class ElasticsearchInstrumentation
    {
        public static function register(): void
        {
            self::hookElasticsearchClient();
            self::hookElasticaClient();
        }

        private static function hookElasticsearchClient(): void
        {
            hook(
                \Elasticsearch\Client::class,
                'performRequest',
                pre: static function (\Elasticsearch\Client $client, array $params, string $class, string $function): void {
                    $instrumentation = new CachedInstrumentation(__CLASS__);
                    [$endpoint] = $params;

                    assert($endpoint instanceof AbstractEndpoint);

                    $endpointReflection = new ReflectionClass($endpoint::class);
                    $endpointOperation = strtolower($endpointReflection->getShortName());
                    $endpointDocId = $endpointReflection->getProperty('id')->getValue($endpoint);

                    $spanBuilder = $instrumentation
                        ->tracer()
                        ->spanBuilder(self::makeSpanName($endpoint))
                        ->setSpanKind(SpanKind::KIND_CLIENT)
                        ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                        ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                        ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_ELASTICSEARCH)
                        ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $endpoint->getMethod())
                        ->setAttribute(TraceAttributes::URL_FULL, $endpoint->getURI())
                        ->setAttribute(TraceAttributes::DB_OPERATION_NAME, $endpointOperation)
                        ->setAttribute('db.elasticsearch.path_parts.index', $endpoint->getIndex())
                        ->setAttribute('db.elasticsearch.path_parts.doc_id', $endpointDocId);

                    $body = $endpoint->getBody();

                    if (is_array($body)) {
                        try {
                            $spanBuilder->setAttribute(
                                TraceAttributes::DB_QUERY_TEXT,
                                json_encode($body, JSON_THROW_ON_ERROR)
                            );
                        } catch (Throwable) {
                        }
                    } elseif (is_string($body)) {
                        $spanBuilder->setAttribute(TraceAttributes::DB_QUERY_TEXT, $body);
                    }

                    $span = $spanBuilder->startSpan();
                    $context = $span->storeInContext(Context::getCurrent());

                    Context::storage()->attach($context);
                },
                post: static function (\Elasticsearch\Client $client, array $params, $result, ?Throwable $exception): void {
                    if ($exception instanceof Missing404Exception) {
                        $exception = null;
                    }

                    $scope = Context::storage()->scope();
                    if ($scope === null) {
                        return;
                    }

                    $scope->detach();
                    $span = Span::fromContext($scope->context());

                    if ($exception !== null) {
                        $span->recordException($exception, [
                            TraceAttributes::EXCEPTION_ESCAPED => true
                        ]);
                        $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                    } else {
                        $span->setStatus(StatusCode::STATUS_OK);
                    }

                    $span->end();
                }
            );
        }

        private static function hookElasticaClient(): void
        {
            hook(
                \FOS\ElasticaBundle\Elastica\Client::class,
                'request',
                pre: static function (Client $client, array $params, string $class, string $function): void {
                    [$path, $method, $data] = $params;
                    $instrumentation = new CachedInstrumentation(__CLASS__);

                    $spanBuilder = $instrumentation
                        ->tracer()
                        ->spanBuilder($path)
                        ->setSpanKind(SpanKind::KIND_CLIENT)
                        ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                        ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                        ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_ELASTICSEARCH)
                        ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $method)
                        ->setAttribute(TraceAttributes::URL_FULL, $path);

                    if (is_array($data)) {
                        try {
                            $spanBuilder->setAttribute(
                                TraceAttributes::DB_QUERY_TEXT,
                                json_encode($data, JSON_THROW_ON_ERROR)
                            );
                        } catch (Throwable) {
                        }
                    } elseif (is_string($data)) {
                        $spanBuilder->setAttribute(TraceAttributes::DB_QUERY_TEXT, $data);
                    }

                    $span = $spanBuilder->startSpan();
                    $context = $span->storeInContext(Context::getCurrent());

                    Context::storage()->attach($context);
                },
                post: static function (Client $client, array $params, $result, ?Throwable $exception): void {
                    if (!$exception instanceof ExceptionInterface) {
                        $exception = null;
                    }

                    $scope = Context::storage()->scope();
                    if ($scope === null) {
                        return;
                    }

                    $scope->detach();
                    $span = Span::fromContext($scope->context());

                    if ($exception !== null) {
                        $span->recordException($exception, [
                            TraceAttributes::EXCEPTION_ESCAPED => true
                        ]);
                        $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                    } else {
                        $span->setStatus(StatusCode::STATUS_OK);
                    }

                    $span->end();
                }
            );
        }

        private static function makeSpanName(AbstractEndpoint $endpoint): string
        {
            if ($endpoint->getMethod() === '') {
                return 'Search elasticsearch';
            }

            $uri = (string) preg_replace('~/_doc/\d+~', '/_doc/{docId}', $endpoint->getURI());
            $uri = (string) preg_replace('~/scroll/[A-Za-z0-9_-]+~', '/scroll/{scrollId}', $uri);

            return sprintf('%s %s', $endpoint->getMethod(), $uri);
        }
    }

    ElasticsearchInstrumentation::register();

    ////////////////////////////EventDispatcherInterface////////////////////////////

    hook(
        EventDispatcherService::class,
        'dispatch',
        pre: static function (EventDispatcherService $dispatcher, array $params, string $class, string $function): void {
            [$event] = $params;
            if ($event === null) {
                return;
            }

            $parent = Context::getCurrent();

            $instrumentation = new CachedInstrumentation(__CLASS__);
            $builder = $instrumentation->tracer()
                ->spanBuilder(sprintf('EVENT DISPATCH %s', $event::class))
                ->setSpanKind(SpanKind::KIND_INTERNAL)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute('type', 'event');

            $span = $builder->startSpan();
            $context = $span->storeInContext($parent);
            Context::storage()->attach($context);
        },
        post: static function (EventDispatcherService $dispatcher, array $params, $return, ?Throwable $exception): void {
            [$event] = $params;
            if ($event === null) {
                return;
            }

            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        }
    );

    ////////////////////////////AsyncService////////////////////////////

    hook(
        AsyncService::class,
        'call',
        pre: static function (): void {
            $parent = Context::getCurrent();

            $instrumentation = new CachedInstrumentation(__CLASS__);
            $builder = $instrumentation->tracer()
                ->spanBuilder('AsyncService')
                ->setSpanKind(SpanKind::KIND_INTERNAL);

            $span = $builder->startSpan();
            $context = $span->storeInContext($parent);
            Context::storage()->attach($context);
        },
        post: static function (): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->end();
        }
    );
}
