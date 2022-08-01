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

namespace App\Domain\Event\Facade;

use App\Domain\Work\Entity\Work;
use App\Application\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Application\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserWorkService;
use App\Domain\WorkStatus\Entity\WorkStatus;

class EventParticipantFacade
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly UserWorkService $userWorkService
    ) {}

    public function getEventParticipantsByUserEvent(
        User $user,
        Event $event
    ): array {
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $workStatus
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
