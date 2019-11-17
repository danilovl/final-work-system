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

use DateTime;
use Doctrine\ORM\{
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    EventType
};
use FinalWork\FinalWorkBundle\Entity\Repository\EventRepository;
use FinalWork\SonataUserBundle\Entity\User;

class EventFacade
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * EventFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->eventRepository = $entityManager->getRepository(Event::class);
    }

    /**
     * @param int $id
     * @return Event|null
     */
    public function find(int $id): ?Event
    {
        return $this->eventRepository->find($id);
    }

    /**
     * @param User $user
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @param EventType|null $eventType
     * @return array
     */
    public function getEventsByOwner(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): array {
        return $this->eventRepository
            ->findAllByOwner($user, $startDate, $endDate, $eventType)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @param EventType|null $eventType
     * @return array
     */
    public function getEventsByParticipant(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): array {
        return $this->eventRepository
            ->findAllByParticipant($user, $startDate, $endDate, $eventType)
            ->getQuery()
            ->getResult();
    }
}
