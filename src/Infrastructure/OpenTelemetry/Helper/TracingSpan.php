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

use Error;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\{
    Span,
    SpanInterface,
    SpanKind,
    StatusCode};
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Throwable;

readonly class TracingSpan
{
    private function __construct(private SpanInterface $state, private ?ScopeInterface $scope) {}

    public static function start(string $name): self
    {
        $span = Globals::tracerProvider()
            ->getTracer(__CLASS__)
            ->spanBuilder($name)
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttribute(SpanAttributes::RECORDED_LOCATION->value, self::calledFrom())
            ->startSpan();

        $scope = $span->activate();

        return new self($span, $scope);
    }

    public function end(): void
    {
        if ($this->scope === null) {
            $this->addErrorEvent(sprintf('Suspicious call of %s', __METHOD__));

            return;
        }

        $this->scope->detach();
        $this->state->end();
    }

    public static function createNewCurrent(): self
    {
        $span = Span::getCurrent();

        return new self($span, null);
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->state->setAttribute($key, TracingHelper::normalizeAttributeValue($value));

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->state->setAttributes(TracingHelper::normalizeAttributeValues($attributes));

        return $this;
    }

    public function addEvent(string $message, array $attributes = []): self
    {
        $this->state->addEvent($message, TracingHelper::normalizeAttributeValues($attributes));

        return $this;
    }

    public function addErrorEvent(string $message, array $attributes = []): self
    {
        $this->recordHandledException(new Error($message), $attributes);

        return $this;
    }

    public function recordHandledException(Throwable $exception, array $attributes = []): self
    {
        $this->state->recordException($exception, [
            TraceAttributes::EXCEPTION_ESCAPED => false,
            ...TracingHelper::normalizeAttributeValues($attributes),
            ...TracingHelper::extractTracingAttributesFromObject($exception),
            SpanAttributes::RECORDED_LOCATION->value => self::calledFrom()
        ]);

        return $this;
    }

    public function markOutcomeAsFailure(?string $description = null): self
    {
        $this->state->setStatus(StatusCode::STATUS_ERROR, $description);

        return $this;
    }

    public function markOutcomeAsSuccess(?string $description = null): self
    {
        $this->state->setStatus(StatusCode::STATUS_OK, $description);

        return $this;
    }

    private static function calledFrom(): string
    {
        $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        return sprintf('%s:%d', $stackTrace[1]['file'] ?? 'unknown', $stackTrace[1]['line'] ?? 'unknown');
    }
}
