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

namespace App\Infrastructure\OpenTelemetry\Helper;

use DateTimeInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\{
    Span,
    StatusCode
};
use Throwable;

readonly class TracingHelper
{
    /**
     * @return array<string, string>
     */
    public static function getCurrentTracingContext(): array
    {
        $span = Span::getCurrent();

        return [
            SpanAttributes::TRACE_ID->value => $span->getContext()->getTraceId(),
            SpanAttributes::SPAN_ID->value => $span->getContext()->getSpanId()
        ];
    }

    public static function markOutcomeAsFailure(?string $description = null): void
    {
        Span::getCurrent()->setStatus(StatusCode::STATUS_ERROR, $description);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function addAttributesToCurrentSpan(array $attributes): void
    {
        Span::getCurrent()->setAttributes(self::normalizeAttributeValues($attributes));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function recordHandledException(Throwable $exception, array $attributes = []): void
    {
        $span = Span::getCurrent();

        $attributes = [
            'exception.escaped' => false,
            ...self::normalizeAttributeValues($attributes),
            ...self::extractTracingAttributesFromObject($exception),
            SpanAttributes::RECORDED_LOCATION->value => self::calledFrom()
        ];

        if ($span->isRecording()) {
            $span->recordException($exception, $attributes);

            return;
        }

        Globals::tracerProvider()
            ->getTracer(__CLASS__)
            ->spanBuilder('Out of tracing scope')
            ->startSpan()
            ->recordException($exception, $attributes)
            ->end();
    }

    public static function normalizeAttributeValues(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $key = 'item-' . $key;
            } elseif ($key === SpanAttributes::SPAN_TYPE->value) {
                $key = '_' . $key;
            } elseif ($key === '' || $value === '') {
                continue;
            }

            $filtered[$key] = self::normalizeAttributeValue($value);
        }

        return $filtered;
    }

    public static function normalizeAttributeValue(mixed $value): array|bool|float|int|string|null
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_object($value)) {
            return $value::class;
        }

        if (is_array($value)) {
            try {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
            }
        }

        if (is_scalar($value) === false && $value !== null) {
            return 'UnsupportedValueType';
        }

        return $value;
    }

    public static function extractTracingAttributesFromObject(object $object): array
    {
        $attributes = [];

        if ($object instanceof Throwable && $object->getPrevious() !== null) {
            $attributes = [
                ...$attributes,
                ...self::extractTracingAttributesFromObject($object->getPrevious())
            ];
        }

        return self::normalizeAttributeValues($attributes);
    }

    private static function calledFrom(): string
    {
        $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        return sprintf('%s:%d', $stackTrace[1]['file'] ?? 'unknown', $stackTrace[1]['line'] ?? 'unknown');
    }
}
