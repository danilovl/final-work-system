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

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Exception\BadRequestException,
    Response,
    JsonResponse
};

readonly class EventCalendarUserReservationWorkHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private EventEventDispatcher $eventEventDispatcherService
    ) {}

    public function __invoke(Event $event, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();
        $ownerWorks = $event->getOwner()->getSupervisorWorks();

        if ($ownerWorks->contains($work) === false) {
            throw new BadRequestException('You cannot reserve this work for the event.');
        }

        $appointmentParticipant = new EventParticipant;
        $appointmentParticipant->setEvent($event);
        $appointmentParticipant->setUser($user);
        $appointmentParticipant->setWork($work);

        $this->entityManagerService->persistAndFlush($appointmentParticipant);
        $event->setParticipant($appointmentParticipant);

        $this->eventEventDispatcherService->onEventCalendarReservation($event);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
