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

namespace App\Application\OpenTelemetry\Cache;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use Psr\Cache\CacheItemPoolInterface;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class CacheItemPoolRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        hook(
            CacheItemPoolInterface::class,
            'getItem',
            pre: static function (CacheItemPoolInterface $object, array $params, string $class, string $function, ?string $filename, ?int $lineno): void {
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
}
