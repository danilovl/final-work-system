<?php declare(strict_types=1);

use App\Application\Service\EventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use Elastica\Client;
use Elastica\Exception\ExceptionInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Endpoints\AbstractEndpoint;
use GuzzleHttp\Exception\{
    ClientException,
    BadResponseException
};
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\{
    ClientInterface,
    RequestOptions,
    RetryMiddleware
};
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Metrics\GaugeInterface;
use OpenTelemetry\SemConv\TraceAttributeValues;
use Predis\Command\CommandInterface;
use Predis\Command\Redis\AUTH;
use Predis\Connection\{
    ConnectionInterface,
    ParametersInterface
};
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode,
    SpanInterface
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\{
    PropagationGetterInterface,
    PropagationSetterInterface
};
use OpenTelemetry\SemConv\TraceAttributes;
use function OpenTelemetry\Instrumentation\hook;

if (extension_loaded('opentelemetry')) {
    final class Helper
    {
        public static function gauge(string $name, ?string $unit = null): GaugeInterface
        {
            return Globals::meterProvider()
                ->getMeter(__CLASS__)
                ->createGauge($name, $unit);
        }
    }

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
            $spanName = sprintf('MYSQL %s', $spanName);

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

                    $spanName = self::makeSpanName($endpoint);
                    $spanName = sprintf('ELASTICA %s', $spanName);

                    $spanBuilder = $instrumentation
                        ->tracer()
                        ->spanBuilder($spanName)
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

                    $spanName = sprintf('ELASTICA %s', $path);

                    $spanBuilder = $instrumentation
                        ->tracer()
                        ->spanBuilder($spanName)
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

    ////////////////////////////Redis////////////////////////////

    class RedisInstrumentation
    {
        public static function register(): void
        {
            $instrumentation = new CachedInstrumentation(__CLASS__);
            $connectionParameters = new stdClass;

            self::hook($instrumentation, $connectionParameters);
        }

        private static function hook(CachedInstrumentation $instrumentation, stdClass $connectionParameters): void
        {
            hook(
                ConnectionInterface::class,
                '__construct',
                pre: static function (ConnectionInterface $client, array $params) use ($connectionParameters): void {
                    $parameters = $params[0] ?? null;

                    if (!$parameters instanceof ParametersInterface) {
                        return;
                    }

                    $parameters = $parameters->toArray();
                    $connectionParameters->scheme = $parameters['scheme'] ?? null;
                    $connectionParameters->host = $parameters['host'] ?? null;
                    $connectionParameters->port = $parameters['port'] ?? null;
                    $connectionParameters->database = $parameters['database'] ?? null;
                }
            );

            hook(
                ConnectionInterface::class,
                'executeCommand',
                pre: static function (ConnectionInterface $client, array $params, string $class, string $function) use ($instrumentation, $connectionParameters): void {
                    [$command] = $params;

                    assert($command instanceof CommandInterface);

                    $spanName = self::makeSpanName($command);
                    $spanName = sprintf('REDIS %s', $spanName);

                    $ignore = [
                        'DoctrineNamespaceCacheKey',
                        'DoctrineSecondLevelCache'
                    ];

                    foreach ($ignore as $value) {
                        if (str_contains($spanName, $value)) {
                            return;
                        }
                    }

                    $spanBuilder = $instrumentation
                        ->tracer()
                        ->spanBuilder($spanName)
                        ->setSpanKind(SpanKind::KIND_INTERNAL)
                        ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                        ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                        ->setAttribute(TraceAttributes::SERVER_ADDRESS, $connectionParameters->host)
                        ->setAttribute(TraceAttributes::NETWORK_TRANSPORT, $connectionParameters->scheme)
                        ->setAttribute(TraceAttributes::NETWORK_PEER_ADDRESS, $connectionParameters->host)
                        ->setAttribute(TraceAttributes::NETWORK_PEER_PORT, $connectionParameters->port)
                        ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_REDIS)
                        ->setAttribute(TraceAttributes::DB_NAMESPACE, $connectionParameters->database)
                        ->setAttribute(TraceAttributes::DB_NAMESPACE, $connectionParameters->database)
                        ->setAttribute(TraceAttributes::DB_OPERATION_NAME, $command->getId())
                        ->setAttribute(TraceAttributes::DB_QUERY_TEXT, self::makeDbStatement($command))
                        ->setAttribute('command.class', $command::class);

                    $parent = Context::getCurrent();
                    $span = $spanBuilder
                        ->setParent($parent)
                        ->startSpan();

                    $span->setStatus(StatusCode::STATUS_OK);

                    $context = $span->storeInContext($parent);
                    Context::storage()->attach($context);
                },
                post: static function (ConnectionInterface $client, array $params, $void, ?Throwable $exception): void {
                    [$command] = $params;

                    assert($command instanceof CommandInterface);

                    $spanName = self::makeSpanName($command);
                    $ignore = [
                        'DoctrineNamespaceCacheKey',
                        'DoctrineSecondLevelCache'
                    ];

                    foreach ($ignore as $value) {
                        if (str_contains($spanName, $value)) {
                            return;
                        }
                    }

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
        }

        private static function makeSpanName(CommandInterface $command): string
        {
            if ($command instanceof AUTH || $command->getId() === 'AUTH') {
                return sprintf('%s ?', $command->getId());
            }

            $arguments = $command->getArguments();
            $firstArgument = array_shift($arguments);

            if ($firstArgument !== null) {
                return sprintf(
                    '%s %s %s',
                    $command->getId(),
                    $firstArgument,
                    implode(' ', array_map(static fn (mixed $value): string => '<?>', $arguments)),
                );
            }

            return $command->getId() !== '' ? $command->getId() : 'Unknown command';
        }

        private static function makeDbStatement(CommandInterface $command): string
        {
            if ($command instanceof AUTH || $command->getId() === 'AUTH') {
                return sprintf('%s ?', $command->getId());
            }

            return sprintf('%s %s', $command->getId(), implode(' ', $command->getArguments()));
        }
    }

    RedisInstrumentation::register();

    ////////////////////////////Guzzle////////////////////////////

    class PsrHeadersPropagationSetter implements PropagationGetterInterface, PropagationSetterInterface
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
         * @param RequestInterface $carrier
         */
        public function keys($carrier): array
        {
            assert($carrier instanceof RequestInterface);

            return array_keys($carrier->getHeaders());
        }

        /**
         * @param RequestInterface $carrier
         */
        public function get($carrier, string $key): ?string
        {
            assert($carrier instanceof RequestInterface);

            return $carrier->getHeader($key)[0] ?? null;
        }

        /**
         * @param RequestInterface $carrier
         */
        public function set(&$carrier, string $key, string $value): void
        {
            assert($carrier instanceof RequestInterface);

            $carrier = $carrier->withHeader($key, $value);
        }
    }

    class GuzzleInstrumentation
    {
        public static function register(): void
        {
            $instrumentation = new CachedInstrumentation(__CLASS__);

            self::hookTransfer($instrumentation);
            self::hookRetryMiddleware($instrumentation);
        }

        private static function hookTransfer(CachedInstrumentation $instrumentation): void
        {
            hook(
                ClientInterface::class,
                'transfer',
                pre: static function (ClientInterface $client, array $params, string $class, string $function) use ($instrumentation): array {
                    return self::startRequestSpan($instrumentation, $client, $params, $function, $class);
                },
                post: static function (ClientInterface $client, array $params, PromiseInterface $promise, ?Throwable $exception): void {
                    self::endRequestSpan($promise, $exception);
                },
            );
        }

        private static function hookRetryMiddleware(CachedInstrumentation $instrumentation): void
        {
            hook(
                RetryMiddleware::class,
                'doRetry',
                pre: static function (RetryMiddleware $middleware, array $params, string $class, string $function) use ($instrumentation): array {
                    return self::startRequestSpan($instrumentation, $middleware, $params, $function, $class);
                },
                post: static function (RetryMiddleware $middleware, array $params, PromiseInterface $promise, ?Throwable $exception): void {
                    self::endRequestSpan($promise, $exception);
                },
            );
        }

        /**
         * @param array<int, mixed> $params
         * @return array<int, mixed>
         */
        private static function startRequestSpan(
            CachedInstrumentation $instrumentation,
            ClientInterface|RetryMiddleware $instance,
            array $params,
            string $function,
            string $class,
        ): array {
            [$request, $options] = $params;

            assert($request instanceof RequestInterface);
            assert(is_array($options));

            $requestUrl = (string) $request->getUri();
            $query = $options[RequestOptions::QUERY] ?? null;

            if ($query !== null && str_contains($requestUrl, '?') === false) {
                if (is_array($query)) {
                    $query = Query::build($query);
                }

                if (is_string($query)) {
                    $requestUrl = sprintf('%s?%s', $requestUrl, $query);
                }
            }

            $spanBuilder = $instrumentation
                ->tracer()
                ->spanBuilder(self::makeSpanName($instance, $request))
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttributes([
                    TraceAttributes::CODE_FUNCTION => $function,
                    TraceAttributes::CODE_NAMESPACE => $class,
                    TraceAttributes::URL_FULL => $requestUrl,
                    TraceAttributes::HTTP_REQUEST_METHOD => $request->getMethod(),
                    TraceAttributes::NETWORK_PROTOCOL_VERSION => $request->getProtocolVersion(),
                    TraceAttributes::USER_AGENT_ORIGINAL => $request->getHeaderLine('User-Agent'),
                    TraceAttributes::HTTP_REQUEST_BODY_SIZE => $request->getHeaderLine('Content-Length'),
                    RequestOptions::TIMEOUT => $options[RequestOptions::TIMEOUT] ?? null,
                    RequestOptions::CONNECT_TIMEOUT => $options[RequestOptions::CONNECT_TIMEOUT] ?? null,
                    RequestOptions::READ_TIMEOUT => $options[RequestOptions::READ_TIMEOUT] ?? null,
                    RequestOptions::SYNCHRONOUS => $options[RequestOptions::SYNCHRONOUS] ?? null,
                    'http.request.header.content-length' => $request->getHeaderLine('Content-Length'),
                    'http.request.header.referer' => $request->getHeader('Referer'),
                ]);

            $currentContext = Context::getCurrent();

            if ($instance instanceof ClientInterface === false) {
                $spanBuilder->setParent(
                    Globals::propagator()->extract($request, PsrHeadersPropagationSetter::instance(), $currentContext),
                );
            }

            $span = $spanBuilder->startSpan();
            $context = $span->storeInContext($currentContext);

            Globals::propagator()->inject($request, PsrHeadersPropagationSetter::instance(), $context);
            Context::storage()->attach($context);

            return [$request];
        }

        private static function endRequestSpan(PromiseInterface $promise, ?Throwable $exception): void
        {
            $scope = Context::storage()->scope();

            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());

            if ($exception !== null) {
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                $span->setAttributes([
                    'exception' => $exception::class,
                    'exceptionMessage' => $exception->getMessage(),
                ]);

                $span->end();
            }

            $promise->then(
                onFulfilled: static function (\Psr\Http\Message\ResponseInterface $response) use ($span) {
                    $span->setAttributes([
                        TraceAttributes::HTTP_RESPONSE_STATUS_CODE => $response->getStatusCode(),
                        TraceAttributes::NETWORK_PROTOCOL_VERSION => $response->getProtocolVersion(),
                        TraceAttributes::HTTP_RESPONSE_BODY_SIZE => $response->getHeaderLine('Content-Length')
                    ]);

                    if ($response->getStatusCode() >= 400) {
                        $span->setStatus(StatusCode::STATUS_ERROR);
                    } else {
                        $span->setStatus(StatusCode::STATUS_OK);
                    }

                    $span->end();

                    return $response;
                },
                onRejected: static function (Throwable $exception) use ($span): void {
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                    $span->setAttributes([
                        'exception' => $exception::class,
                        'exceptionMessage' => $exception->getMessage(),
                    ]);

                    self::recordPossibleSevereException($span, $exception);

                    if ($exception instanceof BadResponseException) {
                        $response = $exception->getResponse();

                        $span->setAttributes([
                            TraceAttributes::HTTP_RESPONSE_STATUS_CODE => $response->getStatusCode(),
                            TraceAttributes::NETWORK_PROTOCOL_VERSION => $response->getProtocolVersion(),
                            TraceAttributes::HTTP_RESPONSE_BODY_SIZE => $response->getHeaderLine('Content-Length')
                        ]);
                    }

                    $span->end();

                    throw $exception;
                },
            );
        }

        private static function makeSpanName(ClientInterface|RetryMiddleware $instance, RequestInterface $request): string
        {
            $spanName = $request->getMethod() !== '' ? $request->getMethod() : 'Unknown';

            if ($request->getUri()->getHost() !== '') {
                $spanName = sprintf(
                    '%s %s://%s',
                    $request->getMethod(),
                    $request->getUri()->getScheme(),
                    $request->getUri()->getHost()
                );
            }

            return match ($instance::class) {
                RetryMiddleware::class => sprintf('[Retry] %s', $spanName),
                default => $spanName,
            };
        }

        private static function recordPossibleSevereException(SpanInterface $span, Throwable $throwable): void
        {
            if ($throwable instanceof ClientException || $throwable->getCode() === 404) {
                return;
            }

            $attributes = [
                TraceAttributes::EXCEPTION_ESCAPED => true
            ];

            $span->recordException($throwable, $attributes);
        }
    }

    GuzzleInstrumentation::register();

    ////////////////////////////CacheItemPoolInterface////////////////////////////

    hook(
        CacheItemPoolInterface::class,
        'getItem',
        pre: static function (CacheItemPoolInterface $object, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
            $key = $params[0] ?? null;
            if ($key === null) {
                return;
            }

            $ignore = [
                'Gedmo__',
                '$GEDMO_LOGGABLE_CLASSMETADATA',
                '__CLASSMETADATA__',
                'DC2_REGION',
                'property_',
            ];

            foreach ($ignore as $value) {
                if (str_contains($key, $value)) {
                    return;
                }
            }

            $parent = Context::getCurrent();

            $spanName = sprintf('CACHE CacheItemPoolInterface::getItem(%s)', $key);

            $instrumentation = new CachedInstrumentation(__CLASS__);
            $builder = $instrumentation->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_INTERNAL)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute('cache.key', $key);

            $span = $builder->startSpan();
            $context = $span->storeInContext($parent);
            Context::storage()->attach($context);
        },
        post: static function (CacheItemPoolInterface $object, array $params): void {
            $key = $params[0] ?? null;
            if ($key === null) {
                return;
            }

            $ignore = [
                'Gedmo__',
                '$GEDMO_LOGGABLE_CLASSMETADATA',
                '__CLASSMETADATA__',
                'DC2_REGION',
                'property_',
            ];

            foreach ($ignore as $value) {
                if (str_contains($key, $value)) {
                    return;
                }
            }

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
