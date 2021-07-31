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

namespace App\Model\Event;

use App\Service\EntityManagerService;
use App\Service\User\UserWorkService;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Entity\{
    Event,
    Work,
    WorkStatus,
    EventParticipant
};
use App\Entity\User;

class EventParticipantFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserWorkService $userWorkService
    ) {
    }

    public function getEventParticipantsByUserEvent(
        User $user,
        Event $event
    ): array {
        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );
        $eventParticipantArray = [];

        /** @var Work $work */
        foreach ($userWorks as $work) {
            $participant = new EventParticipant;
            $participant->setUser($work->getAuthor());
            $participant->setWork($work);
            $participant->setEvent($event);

            if ($event->getParticipant() &&
                $event->getParticipant()->getWork() &&
                $event->getParticipant()->getWork()->getId() === $work->getId()
            ) {
                $event->setParticipant($participant);
            }

            $eventParticipantArray[] = $participant;
        }

        return $eventParticipantArray;
    }
}
