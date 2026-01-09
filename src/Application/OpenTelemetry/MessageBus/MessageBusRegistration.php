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

namespace App\Application\OpenTelemetry\MessageBus;

use App\Application\OpenTelemetry\Messenger\Context\SymfonyMessengerPropagationGetterSetter;
use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\SemConv\{
    TraceAttributeValues,
    TraceAttributes};
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface
};
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class MessageBusRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        hook(
            MessageBusInterface::class,
            'dispatch',
            pre: self::getPreCallbackMessageBus(),
            post: self::getPostCallbackMessageBus()
        );

        hook(
            SenderInterface::class,
            'send',
            pre: self::getPreCallbackSender(),
            post: self::getPostCallbackSender()
        );
    }

    private static function getPreCallbackMessageBus(): callable
    {
        return static function (
            object $target,
            array $params,
            string $class,
            string $function,
            ?string $filename,
            ?int $lineno
        ): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            $message = $params[0];
            $messageClass = get_class(($message instanceof Envelope) ? $message->getMessage() : $message);

            $builder = $instrumentation->tracer()
                ->spanBuilder(sprintf('MESSENGER %s %s', mb_strtoupper($function), $messageClass))
                ->setSpanKind(SpanKind::KIND_PRODUCER)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute('symfony.messenger.bus', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            $transportMessageIdStamp = $message instanceof Envelope
                ? $message->last(TransportMessageIdStamp::class)
                : null;

            if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
                $builder->setAttribute(TraceAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
            }

            $parent = Context::getCurrent();

            $span = $builder
                ->setParent($parent)
                ->startSpan();

            $context = $span->storeInContext($parent);

            if ($message instanceof Envelope) {
                Globals::propagator()->inject($message, SymfonyMessengerPropagationGetterSetter::instance(), $context);
            }

            Context::storage()->attach($context);

            return $params;
        };
    }

    private static function getPostCallbackMessageBus(): callable
    {
        return static function (object $target, array $params, mixed $result, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true,
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        };
    }

    private static function getPreCallbackSender(): callable
    {
        return static function (SenderInterface $bus, array $params, string $class, string $function, ?string $filename, ?int $lineno): array {
            $instrumentation = new CachedInstrumentation(__FILE__);

            /** @var Envelope $envelope */
            $envelope = $params[0];
            $messageClass = get_class($envelope->getMessage());
            $transportMessageIdStamp = $envelope->last(TransportMessageIdStamp::class);

            $builder = $instrumentation
                ->tracer()
                ->spanBuilder(sprintf('MESSENGER SEND %s', $messageClass))
                ->setSpanKind(SpanKind::KIND_PRODUCER)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->setAttribute(TraceAttributes::MESSAGING_SYSTEM, TraceAttributeValues::MESSAGING_SYSTEM_RABBITMQ)
                ->setAttribute(TraceAttributes::MESSAGING_OPERATION_TYPE, TraceAttributeValues::MESSAGING_OPERATION_TYPE_PUBLISH)
                ->setAttribute('symfony.messenger.transport', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
                $builder->setAttribute(TraceAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
            }

            $parent = Context::getCurrent();

            $span = $builder
                ->setParent($parent)
                ->startSpan();

            $context = $span->storeInContext($parent);

            Globals::propagator()->inject($envelope, SymfonyMessengerPropagationGetterSetter::instance(), $context);
            Context::storage()->attach($context);

            return $params;
        };
    }

    public static function getPostCallbackSender(): callable
    {
        return static function (SenderInterface $sender, array $params, ?Envelope $result, ?Throwable $exception): void {
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
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        };
    }
}
