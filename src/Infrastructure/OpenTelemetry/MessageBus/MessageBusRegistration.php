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

namespace App\Infrastructure\OpenTelemetry\MessageBus;

use App\Infrastructure\OpenTelemetry\Messenger\Context\SymfonyMessengerPropagationGetterSetter;
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\Attributes\CodeAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\{
    DeploymentIncubatingAttributes,
    MessagingIncubatingAttributes
};
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\{
    Envelope,
    MessageBusInterface
};
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
                ->setAttribute(CodeAttributes::CODE_FUNCTION_NAME, $function)
                ->setAttribute(CodeAttributes::CODE_FILE_PATH, $filename)
                ->setAttribute(CodeAttributes::CODE_LINE_NUMBER, $lineno)
                ->setAttribute('symfony.messenger.bus', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            $transportMessageIdStamp = $message instanceof Envelope
                ? $message->last(TransportMessageIdStamp::class)
                : null;

            if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
                $builder->setAttribute(MessagingIncubatingAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
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
            $span->setAttribute(DeploymentIncubatingAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception);
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
                ->setAttribute(CodeAttributes::CODE_FUNCTION_NAME, $function)
                ->setAttribute(CodeAttributes::CODE_FILE_PATH, $filename)
                ->setAttribute(CodeAttributes::CODE_LINE_NUMBER, $lineno)
                ->setAttribute(MessagingIncubatingAttributes::MESSAGING_SYSTEM, MessagingIncubatingAttributes::MESSAGING_SYSTEM_VALUE_RABBITMQ)
                ->setAttribute(MessagingIncubatingAttributes::MESSAGING_OPERATION_TYPE, MessagingIncubatingAttributes::MESSAGING_OPERATION_TYPE_VALUE_SEND)
                ->setAttribute('symfony.messenger.transport', $class)
                ->setAttribute('symfony.messenger.message', $messageClass)
                ->setAttribute('type', 'dispatch');

            if ($transportMessageIdStamp instanceof TransportMessageIdStamp) {
                $builder->setAttribute(MessagingIncubatingAttributes::MESSAGING_MESSAGE_ID, $transportMessageIdStamp->getId());
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
            $span->setAttribute(DeploymentIncubatingAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            if ($exception !== null) {
                $span->recordException($exception);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        };
    }
}
