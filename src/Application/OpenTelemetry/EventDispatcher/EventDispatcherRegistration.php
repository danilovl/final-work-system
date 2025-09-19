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

namespace App\Application\OpenTelemetry\EventDispatcher;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use App\Application\Service\EventDispatcherService;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class EventDispatcherRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        hook(EventDispatcherService::class, 'dispatch', pre: self::getPreCallback(), post: self::getPostCallback());
    }

    private static function getPreCallback(): callable
    {
        return static function (EventDispatcherService $dispatcher, array $params, string $class, string $function): void {
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
        };
    }

    private static function getPostCallback(): callable
    {
        return static function (EventDispatcherService $dispatcher, array $params, $return, ?Throwable $exception): void {
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
        };
    }
}
