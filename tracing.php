<?php declare(strict_types=1);

use Danilovl\AsyncBundle\Service\AsyncService;
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
