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

use DateTime;
use App\Entity\{
    Event,
    EventType
};
use App\Repository\EventRepository;
use App\Entity\User;

class EventFacade
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function find(int $id): ?Event
    {
        return $this->eventRepository->find($id);
    }

    public function getEventsByOwner(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): array {
        return $this->eventRepository
            ->allByOwner($user, $startDate, $endDate, $eventType)
            ->getQuery()
            ->getResult();
    }

    public function getEventsByParticipant(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): array {
        return $this->eventRepository
            ->allByParticipant($user, $startDate, $endDate, $eventType)
            ->getQuery()
            ->getResult();
    }
}
