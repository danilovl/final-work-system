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

namespace App\Application\ElasticApm\EventListener;

use App\Application\ElasticApm\ElasticApmHelper;
use Override;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\{
    ConsoleErrorEvent,
    ConsoleCommandEvent,
    ConsoleTerminateEvent
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ConsoleListener implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::TERMINATE => 'onTerminate',
            ConsoleEvents::ERROR => 'onError'
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if ($command === null) {
            return;
        }

        ElasticApmHelper::addContextToCurrentTransaction([
            'command' => $command->getName(),
        ], 'console');
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        ElasticApmHelper::addContextToCurrentTransaction([
            'exit_code' => $event->getExitCode()
        ], 'console');

        ElasticApmHelper::endCurrentSpan();
        ElasticApmHelper::endCurrentTransaction();
    }

    public function onError(ConsoleErrorEvent $event): void
    {
        ElasticApmHelper::createErrorFromThrowable($event->getError());
    }
}
