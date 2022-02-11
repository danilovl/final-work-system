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

use App\Application\DataTransferObject\Repository\EventData;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Repository\EventRepository;

class EventFacade
{
    public function __construct(private EventRepository $eventRepository)
    {
    }

    public function find(int $id): ?Event
    {
        return $this->eventRepository->find($id);
    }

    public function getEventsByOwner(EventData $eventData): array
    {
        return $this->eventRepository
            ->allByOwner($eventData)
            ->getQuery()
            ->getResult();
    }

    public function getEventsByParticipant(EventData $eventData): array
    {
        return $this->eventRepository
            ->allByParticipant($eventData)
            ->getQuery()
            ->getResult();
    }
}
