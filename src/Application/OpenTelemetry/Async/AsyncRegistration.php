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

namespace App\Application\OpenTelemetry\Async;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use Danilovl\AsyncBundle\Service\AsyncService;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind
};
use OpenTelemetry\Context\Context;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class AsyncRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        hook(AsyncService::class, 'call', pre: self::getPreCallback(), post: self::getPostCallback());
    }

    private static function getPreCallback(): callable
    {
        return static function (): void {
            $parent = Context::getCurrent();

            $instrumentation = new CachedInstrumentation(__CLASS__);
            $builder = $instrumentation->tracer()
                ->spanBuilder('AsyncService')
                ->setSpanKind(SpanKind::KIND_INTERNAL);

            $span = $builder->startSpan();
            $context = $span->storeInContext($parent);
            Context::storage()->attach($context);
        };
    }

    private static function getPostCallback(): callable
    {
        return static function (): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->end();
        };
    }
}
