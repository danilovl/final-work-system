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

namespace App\Domain\Event\Http\Api;

use App\Domain\Event\DTO\Api\EventDTO;
use App\Domain\Event\DTO\Api\Input\EventCreateInput;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Model\EventModel;
use App\Domain\User\Service\UserService;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use DateTimeZone;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\EventType\Bus\Query\EventType\{GetEventTypeQuery, GetEventTypeQueryResult};
use App\Domain\EventAddress\Bus\Query\EventAddress\{GetEventAddressQuery, GetEventAddressQueryResult};

readonly class EventCreateHandle
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private UserService $userService,
        private EventFactory $eventFactory,
        private ObjectToDtoMapperInterface $objectToDtoMapper,
        private EventEventDispatcher $eventEventDispatcher
    ) {}

    public function __invoke(EventCreateInput $input): EventDTO
    {
        $user = $this->userService->getUser();

        $getEventTypeQuery = GetEventTypeQuery::create($input->typeId);
        /** @var GetEventTypeQueryResult $getEventTypeQueryResult */
        $getEventTypeQueryResult = $this->queryBus->handle($getEventTypeQuery);
        $eventType = $getEventTypeQueryResult->eventType;
        if ($eventType === null) {
            throw new NotFoundHttpException('Event type not found');
        }

        $eventAddress = null;
        if ($input->addressId !== null) {
            $getEventAddressQuery = GetEventAddressQuery::create($input->addressId);
            /** @var GetEventAddressQueryResult $getEventAddressQueryResult */
            $getEventAddressQueryResult = $this->queryBus->handle($getEventAddressQuery);
            $eventAddress = $getEventAddressQueryResult->eventAddress;
            if ($eventAddress === null) {
                throw new NotFoundHttpException('Event address not found');
            }
        }

        $eventModel = new EventModel;
        $eventModel->owner = $user;
        $eventModel->type = $eventType;
        $eventModel->name = $input->name;
        $eventModel->address = $eventAddress;
        $timezone = new DateTimeZone(date_default_timezone_get());
        $eventModel->start = $input->start->setTimezone($timezone);
        $eventModel->end = $input->end->setTimezone($timezone);

        $event = $this->eventFactory->flushFromModel($eventModel);

        $this->eventEventDispatcher->onEventCalendarCreate($event);

        /** @var EventDTO $eventDTO */
        $eventDTO = $this->objectToDtoMapper->map($event, EventDTO::class);

        return $eventDTO;
    }
}
