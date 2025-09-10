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

namespace App\Application\Messenger;

use App\Application\Interfaces\Bus\{
    CommandInterface,
    CommandBusInterface
};
use Symfony\Component\Messenger\{
    Envelope,
    HandleTrait,
    MessageBusInterface
};

class MessengerCommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(private MessageBusInterface $messageBus) {}

    public function dispatch(CommandInterface $command): Envelope
    {
        return $this->messageBus->dispatch($command);
    }

    /**
     * @template T of object
     * @param CommandInterface $command
     * @return object
     */
    public function dispatchResult(CommandInterface $command): object
    {
        return $this->handle($command);
    }
}
