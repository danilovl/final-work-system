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

namespace FinalWork\FinalWorkBundle\Model\Event;

use Doctrine\ORM\{
    EntityManager,
    ORMException
};
use FinalWork\FinalWorkBundle\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    Work,
    WorkStatus,
    EventParticipant
};
use FinalWork\SonataUserBundle\Entity\User;

class EventParticipantFacade
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * EventParticipantFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param User $user
     * @param Event $event
     * @return array
     *
     * @throws ORMException
     */
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
            $participant = new EventParticipant();
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
