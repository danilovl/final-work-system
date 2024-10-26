<?php
declare(strict_types=1);

namespace App\Application\ElasticApm\Messenger;

use App\Application\ElasticApm\ElasticApmHelper;
use App\Application\Messenger\EmailNotification\EmailNotificationMessage;
use App\Application\Messenger\Loggable\LoggableMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\{
    StackInterface,
    MiddlewareInterface
};
use Throwable;

class ApmMessengerMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $name = $this->getNameFromMessage($envelope);
        if ($name === null) {
            return $stack->next()->handle($envelope, $stack);
        }

        ElasticApmHelper::beginCurrentTransaction($name, 'messenger');

        ElasticApmHelper::addContextToCurrentTransaction([
            'envelope_class' => $envelope->getMessage()::class
        ], 'messenger');

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (Throwable $throwable) {
            ElasticApmHelper::createErrorFromThrowable($throwable);

            throw $throwable;
        } finally {
            ElasticApmHelper::endCurrentTransaction();
        }

        return $envelope;
    }

    private function getNameFromMessage(Envelope $envelope): ?string
    {
        return match ($envelope->getMessage()::class) {
            EmailNotificationMessage::class => 'email handler',
            LoggableMessage::class => 'loggable handler',
            default => null
        };
    }
}
