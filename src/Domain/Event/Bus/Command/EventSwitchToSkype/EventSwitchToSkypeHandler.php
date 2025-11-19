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

namespace App\Domain\Event\Bus\Command\EventSwitchToSkype;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Application\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\EventAddress\Facade\EventAddressFacade;

readonly class EventSwitchToSkypeHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private EventAddressFacade $eventAddressFacade,
        private EventEventDispatcher $eventEventDispatcher
    ) {}

    public function __invoke(EventSwitchToSkypeCommand $command): ?Event
    {
        $event = $command->event;
        $eventAddressSkype = $this->eventAddressFacade->getSkypeByOwner($event->getOwner());

        if ($eventAddressSkype === null) {
            return null;
        }

        $event->setAddress($eventAddressSkype);
        $this->entityManagerService->flush();
        $this->eventEventDispatcher->onEventSwitchToSkype($event);

        return $event;
    }
}
