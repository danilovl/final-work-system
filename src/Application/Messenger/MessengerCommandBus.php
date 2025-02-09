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
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MessengerCommandBus implements CommandBusInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public function dispatch(CommandInterface $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
