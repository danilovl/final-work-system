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
use App\Domain\Event\Facade\EventCalendarFacade;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Model\EventModel;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Facade\EventAddressFacade;
use App\Domain\EventCalendar\DTO\Api\Input\EventCalendarEventInput;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventType\Entity\EventType;
use App\Domain\EventType\Facade\EventTypeFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Facade\WorkFacade;
use App\Infrastructure\Service\EntityManagerService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class EventCalendarManageCreateEventHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private EventFactory $eventFactory,
        private EventEventDispatcher $eventEventDispatcher,
        private EventCalendarFacade $eventCalendarFacade,
        private UserFacade $userFacade,
        private WorkFacade $workFacade,
        private EventAddressFacade $eventAddressFacade,
        private EventTypeFacade $eventTypeFacade
    ) {}

    public function __invoke(EventCalendarEventInput $input): JsonResponse
    {
        $user = $this->userService->getUser();

        /** @var EventType|null $eventType */
        $eventType = $this->eventTypeFacade->findById($input->typeId);
        if ($eventType === null) {
            throw new NotFoundHttpException('Event type not found');
        }

        /** @var EventAddress|null $eventAddress */
        $eventAddress = $this->eventAddressFacade->findById($input->addressId);
        if ($eventAddress === null) {
            throw new NotFoundHttpException('Event address not found');
        }

        /** @var User|null $participantUser */
        $participantUser = $this->userFacade->findById($input->userId);
        if ($participantUser === null) {
            throw new NotFoundHttpException('Participant user not found');
        }

        /** @var Work|null $participantWork */
        $participantWork = $this->workFacade->findById($input->workId);
        if ($participantWork === null) {
            throw new NotFoundHttpException('Participant work not found');
        }

        $eventParticipant = new EventParticipant;
        $eventParticipant->setUser($participantUser);
        $eventParticipant->setWork($participantWork);

        $startDateTime = new DateTime($input->start);
        $endDateTime = new DateTime($input->end);

        $eventModel = new EventModel;
        $eventModel->owner = $user;
        $eventModel->type = $eventType;
        $eventModel->name = $input->name;
        $eventModel->address = $eventAddress;
        $eventModel->participant = $eventParticipant;
        $eventModel->start = $startDateTime;
        $eventModel->end = $endDateTime;

        $event = $this->eventFactory->flushFromModel($eventModel);

        $eventParticipant->setEvent($event);
        $this->entityManagerService->flush();

        $event->setParticipant($eventParticipant);

        $this->eventEventDispatcher->onEventCalendarCreate($event);
        $event = $this->eventCalendarFacade->convertEventCreateToArray(event: $event, isApi: true);;

        return new JsonResponse($event);
    }
}
