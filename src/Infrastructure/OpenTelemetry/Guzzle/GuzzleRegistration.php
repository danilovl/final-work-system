<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Infrastructure\OpenTelemetry\Guzzle;

use App\Infrastructure\OpenTelemetry\Guzzle\Contex\PsrHeadersPropagationSetter;
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use GuzzleHttp\{
    RequestOptions,
    RetryMiddleware
};
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\{
    BadResponseException,
    ClientException
};
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Query;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanInterface,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\Attributes\{
    CodeAttributes,
    HttpAttributes,
    NetworkAttributes,
    UrlAttributes,
    UserAgentAttributes
};
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class GuzzleRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
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
                CodeAttributes::CODE_FUNCTION_NAME => $class . $function,
                UrlAttributes::URL_FULL => $requestUrl,
                HttpAttributes::HTTP_REQUEST_METHOD => $request->getMethod(),
                NetworkAttributes::NETWORK_PROTOCOL_VERSION => $request->getProtocolVersion(),
                UserAgentAttributes::USER_AGENT_ORIGINAL => $request->getHeaderLine('User-Agent'),
                'http.request.body.size' => $request->getHeaderLine('Content-Length'),
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
            onFulfilled: static function (ResponseInterface $response) use ($span) {
                $span->setAttributes([
                    HttpAttributes::HTTP_RESPONSE_STATUS_CODE => $response->getStatusCode(),
                    NetworkAttributes::NETWORK_PROTOCOL_VERSION => $response->getProtocolVersion(),
                    'http.response.body.size' => $response->getHeaderLine('Content-Length')
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
                        HttpAttributes::HTTP_RESPONSE_STATUS_CODE => $response->getStatusCode(),
                        NetworkAttributes::NETWORK_PROTOCOL_VERSION => $response->getProtocolVersion(),
                        'http.response.body.size' => $response->getHeaderLine('Content-Length')
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
            'exception.escaped' => true
        ];

        $span->recordException($throwable, $attributes);
    }
}
