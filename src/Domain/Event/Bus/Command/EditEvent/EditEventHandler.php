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

namespace App\Domain\Event\Bus\Command\EditEvent;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Factory\EventFactory;

readonly class EditEventHandler implements CommandHandlerInterface
{
    public function __construct(
        private EventFactory $eventFactory,
        private EventEventDispatcher $eventEventDispatcher
    ) {}

    public function __invoke(EditEventCommand $command): void
    {
        $this->eventFactory->flushFromModel($command->eventModel, $command->event);
        $this->eventEventDispatcher->onEventEdit($command->event);
    }
}
