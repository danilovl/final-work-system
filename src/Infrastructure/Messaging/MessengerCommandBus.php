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

namespace App\Infrastructure\Messaging;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandInterface};
use Symfony\Component\Messenger\{
    HandleTrait,
    MessageBusInterface
};
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class MessengerCommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(private MessageBusInterface $messageBus) {}

    public function dispatch(CommandInterface $command): void
    {
        try {
            $this->messageBus->dispatch($command);
        } catch (HandlerFailedException $exception) {
            $this->throwWrappedException($exception);
        }
    }

    /**
     * @param CommandInterface $command
     * @return object|null
     */
    public function dispatchResult(CommandInterface $command): ?object
    {
        try {
            /** @var object|null $result */
            $result = $this->handle($command);
        } catch (HandlerFailedException $exception) {
            $this->throwWrappedException($exception);
        }

        return $result;
    }

    private function throwWrappedException(HandlerFailedException $exception): never
    {
        $wrappedExceptions = $exception->getWrappedExceptions();

        $firstException = reset($wrappedExceptions);

        throw $firstException !== false ? $firstException : $exception;
    }
}
