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

use App\Application\Service\EntityManagerService;
use App\Domain\Event\Entity\Event;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\{
    WorkUserTypeConstant
};
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;

readonly class EventParticipantFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserWorkService $userWorkService
    ) {}

    public function getEventParticipantsByUserEvent(
        User $user,
        Event $event
    ): array {
        /** @var WorkStatus $workStatus */
        $workStatus = $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

        $userWorks = $this->userWorkService->getWorkBy(
            $user,
            WorkUserTypeConstant::SUPERVISOR->value,
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
