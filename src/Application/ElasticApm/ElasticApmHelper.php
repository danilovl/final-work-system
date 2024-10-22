<?php
declare(strict_types=1);

namespace App\Application\ElasticApm;

use Elastic\Apm\{
    ElasticApm,
    SpanInterface,
    TransactionInterface
};
use Throwable;

class ElasticApmHelper
{
    public static function getCurrentTransaction(): TransactionInterface
    {
        return ElasticApm::getCurrentTransaction();
    }

    public static function endCurrentTransaction(): void
    {
        ElasticApm::getCurrentTransaction()->end();
    }

    public static function getCurrentSpan(): SpanInterface
    {
        return ElasticApm::getCurrentTransaction()->getCurrentSpan();
    }

    public static function endCurrentSpan(): void
    {
        ElasticApm::getCurrentTransaction()->getCurrentSpan()->end();
    }

    /**
     * @return array<string, string>
     */
    public static function getApmContext(): array
    {
        $transaction = ElasticApm::getCurrentTransaction();

        return [
            'traceId' => $transaction->getTraceId(),
            'transactionId' => $transaction->getId(),
            'spanId' => $transaction->getCurrentSpan()->getId(),
        ];
    }

    public static function createErrorFromThrowable(Throwable $exception): void
    {
        ElasticApm::createErrorFromThrowable($exception);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public static function addContextToCurrentTransaction(array $context, string $prefix = ''): void
    {
        self::addContext(ElasticApm::getCurrentTransaction(), $context, $prefix);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public static function addContextToCurrentSpan(array $context): void
    {
        self::addContext(ElasticApm::getCurrentTransaction()->getCurrentSpan(), $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public static function beginSpanWithContext(
        SpanNameEnum $name,
        SpanTypeEnum $type,
        ?SpanSubtypeEnum $subtype = null,
        array $context = [],
        ?string $action = null,
        ?float $timestamp = null,
    ): SpanInterface {
        $span = ElasticApm::getCurrentTransaction()->beginCurrentSpan(
            $name->value,
            $type->value,
            $subtype?->value,
            $action,
            $timestamp,
        );

        self::addContext($span, $context);

        return $span;
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public static function addContext(
        TransactionInterface|SpanInterface $span,
        array $context = [],
        string $prefix = ''
    ): void {
        if (!empty($prefix)) {
            $prefix .= '.';
        }

        foreach ($context as $key => $value) {
            if (is_string($key) === false) {
                $key = 'item' . $key;
            }

            if (is_array($value)) {
                try {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                } catch (Throwable) {
                }
            }

            $span->context()->setLabel($prefix. $key, $value);
        }
    }
}
