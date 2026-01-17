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

namespace App\Infrastructure\OpenTelemetry\HttpKernel;

use App\Infrastructure\OpenTelemetry\HttpKernel\Context\RequestPropagationGetter;
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanInterface,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\{
    HttpKernel,
    HttpKernelInterface
};
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 100])]
class HttpKernelRegistration implements OpenTelemetryRegistrationInterface
{
    public static bool $isHandleThrowable = false;

    public static function registration(): void
    {
        hook(HttpKernel::class, 'handle', pre: self::getPreCallback(), post: self::getPostCallback());

        hook(HttpKernel::class, 'terminate', post: static function (): void {
            Context::storage()->scope()?->detach();
        });

        hook(HttpKernel::class, 'terminateWithException', post: static function (): void {
            Context::storage()->scope()?->detach();
        });

        hook(HttpKernel::class, 'handleThrowable', pre: static function (HttpKernel $kernel, array $params): void {
            /** @var Throwable $throwable */
            $throwable = $params[0];

            Span::getCurrent()
                ->recordException($throwable, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);

            self::$isHandleThrowable = true;
        });
    }

    public static function getPreCallback(): callable
    {
        return static function (HttpKernel $kernel, array $params, string $class, string $function, ?string $filename, ?int $lineno): array {
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
        };
    }

    public static function getPostCallback(): callable
    {
        return static function (HttpKernel $kernel, array $params, ?Response $response, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            if (self::$isHandleThrowable) {
                $scope->detach();
                self::$isHandleThrowable = false;
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
                $contentLength = mb_strlen($response->getContent());
            }

            $span->setAttribute(TraceAttributes::HTTP_RESPONSE_BODY_SIZE, $contentLength);

            $span->end();
        };
    }
}
