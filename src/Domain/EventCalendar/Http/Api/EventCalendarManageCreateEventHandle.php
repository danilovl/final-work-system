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

namespace App\Domain\EventCalendar\Http\Api;

use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\EventCalendar\Facade\EventCalendarFacade;
use App\Application\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\User\Bus\Query\User\{GetUserQuery, GetUserQueryResult};
use App\Domain\Work\Bus\Query\Work\{GetWorkQuery, GetWorkQueryResult};
use App\Domain\EventType\Bus\Query\EventType\{GetEventTypeQuery, GetEventTypeQueryResult};
use App\Domain\EventAddress\Bus\Query\EventAddress\{GetEventAddressQuery, GetEventAddressQueryResult};
use App\Domain\Event\Bus\Command\CreateEvent\CreateEventCommand;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventCalendar\DTO\Api\Input\EventCalendarEventInput;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\UserService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class EventCalendarManageCreateEventHandle
{
    public function __construct(
        private UserService $userService,
        private CommandBusInterface $commandBus,
        private EventEventDispatcher $eventEventDispatcher,
        private EventCalendarFacade $eventCalendarFacade,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(EventCalendarEventInput $input): JsonResponse
    {
        $user = $this->userService->getUser();

        $getEventTypeQuery = GetEventTypeQuery::create($input->typeId);
        /** @var GetEventTypeQueryResult $getEventTypeQueryResult */
        $getEventTypeQueryResult = $this->queryBus->handle($getEventTypeQuery);
        $eventType = $getEventTypeQueryResult->eventType;
        if ($eventType === null) {
            throw new NotFoundHttpException('Event type not found');
        }

        $getEventAddressQuery = GetEventAddressQuery::create($input->addressId);
        /** @var GetEventAddressQueryResult $getEventAddressQueryResult */
        $getEventAddressQueryResult = $this->queryBus->handle($getEventAddressQuery);
        $eventAddress = $getEventAddressQueryResult->eventAddress;
        if ($eventAddress === null) {
            throw new NotFoundHttpException('Event address not found');
        }

        $eventParticipant = null;
        
        if ($input->userId !== null && $input->workId !== null) {
            $getUserQuery = GetUserQuery::create($input->userId);
            /** @var GetUserQueryResult $getUserQueryResult */
            $getUserQueryResult = $this->queryBus->handle($getUserQuery);
            $participantUser = $getUserQueryResult->user;
            if ($participantUser === null) {
                throw new NotFoundHttpException('Participant user not found');
            }

            $getWorkQuery = GetWorkQuery::create($input->workId);
            /** @var GetWorkQueryResult $getWorkQueryResult */
            $getWorkQueryResult = $this->queryBus->handle($getWorkQuery);
            $participantWork = $getWorkQueryResult->work;
            if ($participantWork === null) {
                throw new NotFoundHttpException('Participant work not found');
            }

            $eventParticipant = new EventParticipant;
            $eventParticipant->setUser($participantUser);
            $eventParticipant->setWork($participantWork);
        }

        $startDateTime = new DateTimeImmutable($input->start);
        $endDateTime = new DateTimeImmutable($input->end);

        $eventModel = new EventModel;
        $eventModel->owner = $user;
        $eventModel->type = $eventType;
        $eventModel->name = $input->name;
        $eventModel->address = $eventAddress;
        $eventModel->participant = $eventParticipant;
        $eventModel->start = $startDateTime;
        $eventModel->end = $endDateTime;

        $createEventCommand = CreateEventCommand::create($eventModel);
        /** @var Event $event */
        $event = $this->commandBus->dispatchResult($createEventCommand);

        $this->eventEventDispatcher->onEventCalendarCreate($event);
        $event = $this->eventCalendarFacade->convertEventCreateToArray(event: $event, isApi: true);;

        return new JsonResponse($event);
    }
}
