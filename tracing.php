<?php declare(strict_types=1);

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
