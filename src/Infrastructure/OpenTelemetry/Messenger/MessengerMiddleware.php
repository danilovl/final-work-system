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

namespace App\Infrastructure\OpenTelemetry\Messenger;

use App\Infrastructure\OpenTelemetry\Messenger\Context\{
    EnvelopeIsTracedStamp
};
use App\Infrastructure\OpenTelemetry\Messenger\Context\SymfonyMessengerPropagationGetterSetter;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\Attributes\CodeAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\MessagingIncubatingAttributes;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\{
    MiddlewareInterface,
    StackInterface
};
use Symfony\Component\Messenger\Stamp\{
    ConsumedByWorkerStamp,
    RedeliveryStamp,
    TransportMessageIdStamp
};
use Throwable;

class MessengerMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) === null || !extension_loaded('opentelemetry')) {
            return $stack->next()->handle($envelope, $stack);
        }

        $messageClass = $envelope->getMessage()::class;

        $producerContext = Globals::propagator()->extract(
            $envelope,
            SymfonyMessengerPropagationGetterSetter::instance(),
        );

        $messagingSystem = MessagingIncubatingAttributes::MESSAGING_SYSTEM_VALUE_RABBITMQ;

        $span = Globals::tracerProvider()
            ->getTracer(__CLASS__)
            ->spanBuilder(sprintf('Consume %s', $messageClass))
            ->setParent($producerContext)
            ->setSpanKind(SpanKind::KIND_CONSUMER)
            ->setAttribute(CodeAttributes::CODE_FUNCTION_NAME, 'handle')
            ->setAttribute(MessagingIncubatingAttributes::MESSAGING_SYSTEM, $messagingSystem)
            ->setAttribute(MessagingIncubatingAttributes::MESSAGING_OPERATION_TYPE, MessagingIncubatingAttributes::MESSAGING_OPERATION_TYPE_VALUE_RECEIVE)
            ->setAttribute('type', 'messenger')
            ->addLink(Span::fromContext($producerContext)->getContext())
            ->startSpan();

        $envelope = $envelope->with(new EnvelopeIsTracedStamp);

        $transportMessageIdStamp = $envelope->last(TransportMessageIdStamp::class);
        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);

        if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
            $span->setAttribute(MessagingIncubatingAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
        }

        if ($redeliveryStamp instanceof RedeliveryStamp) {
            $span->setAttribute('retryAttemptNumber', $redeliveryStamp->getRetryCount());
        }

        $scope = $span->activate();

        try {
            $handledEnvelope = $stack->next()->handle($envelope, $stack);
            $span->setStatus(StatusCode::STATUS_OK);

            return $handledEnvelope;
        } catch (Throwable $exception) {
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

            $exceptionToRecord = $exception;

            if ($exceptionToRecord instanceof HandlerFailedException) {
                $exceptionToRecord = $exception->getPrevious() ?? $exception;
            }

            $span->recordException($exceptionToRecord, [
                'exception.escaped' => true
            ]);

            throw $exception;
        } finally {
            $scope->detach();
            $span->end();

            $tracerProvider = Globals::tracerProvider();
            if ($tracerProvider instanceof TracerProvider) {
                $tracerProvider->forceFlush();
            }
        }
    }
}
