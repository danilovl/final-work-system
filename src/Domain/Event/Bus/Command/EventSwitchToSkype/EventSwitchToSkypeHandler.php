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

use App\Application\Interfaces\Bus\{CommandHandlerInterface, QueryBusInterface};
use App\Domain\EventAddress\Bus\Query\SkypeEventAddress\{GetSkypeEventAddressQuery, GetSkypeEventAddressQueryResult};
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;

readonly class EventSwitchToSkypeHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private QueryBusInterface $queryBus,
        private EventEventDispatcher $eventEventDispatcher
    ) {}

    public function __invoke(EventSwitchToSkypeCommand $command): ?Event
    {
        $event = $command->event;
        $eventOwner = $event->getOwner();
        $getSkypeEventAddressQuery = GetSkypeEventAddressQuery::create($eventOwner);
        /** @var GetSkypeEventAddressQueryResult $getSkypeEventAddressQueryResult */
        $getSkypeEventAddressQueryResult = $this->queryBus->handle($getSkypeEventAddressQuery);
        $eventAddressSkype = $getSkypeEventAddressQueryResult->eventAddress;

        if ($eventAddressSkype === null) {
            return null;
        }

        $event->setAddress($eventAddressSkype);
        $this->entityManagerService->flush();
        $this->eventEventDispatcher->onEventSwitchToSkype($event);

        return $event;
    }
}
