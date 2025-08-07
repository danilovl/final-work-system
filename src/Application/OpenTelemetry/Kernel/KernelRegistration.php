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

namespace App\Application\OpenTelemetry\Kernel;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use App\Application\OpenTelemetry\Redis\RedisRegistration;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind
};
use OpenTelemetry\Context\Context;
use Symfony\Component\HttpKernel\Kernel;
use function OpenTelemetry\Instrumentation\hook;

class KernelRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        RedisRegistration::registration();

        hook(
            Kernel::class,
            'boot',
            pre: static function (): void {
                $instrumentation = new CachedInstrumentation(__CLASS__);
                $builder = $instrumentation->tracer()
                    ->spanBuilder('boot')
                    ->setSpanKind(SpanKind::KIND_SERVER)
                    ->setAttribute('type', 'boot');

                $builder->startSpan();
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
}
