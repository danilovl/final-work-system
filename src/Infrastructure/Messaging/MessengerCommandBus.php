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

use App\Application\Interfaces\Bus\{
    CommandBusInterface,
    CommandInterface
};
use Symfony\Component\Messenger\{
    HandleTrait,
    MessageBusInterface
};

class MessengerCommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(private MessageBusInterface $messageBus) {}

    public function dispatch(CommandInterface $command): void
    {
        $this->messageBus->dispatch($command);
    }

    /**
     * @param CommandInterface $command
     * @return object|null
     */
    public function dispatchResult(CommandInterface $command): ?object
    {
        /** @var object|null $result */
        $result = $this->handle($command);

        return $result;
    }
}
