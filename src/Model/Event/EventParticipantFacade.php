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

use App\Services\EntityManagerService;
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
    private EntityManagerService $em;

    public function __construct(EntityManagerService $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getEventParticipantsByUserEvent(
        User $user,
        Event $event
    ): array {
        $userWorks = $user->getWorkBy(
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $this->em->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
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
