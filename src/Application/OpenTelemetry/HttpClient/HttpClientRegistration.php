<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\OpenTelemetry\HttpClient;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration')]
class HttpClientRegistration implements OpenTelemetryRegistrationInterface
{
    public function registration(): void
    {
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

                if (self::supportsProgress($class) === false) {
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

                if ($response !== null && self::supportsProgress(get_class($client)) === false) {
                    $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $response->getStatusCode());

                    if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
                        $span->setStatus(StatusCode::STATUS_ERROR);
                    }
                }
            }
        );
    }

    private static function supportsProgress(string $class): bool
    {
        return $class !== 'ApiPlatform\Symfony\Bundle\Test\Client';
    }
}
