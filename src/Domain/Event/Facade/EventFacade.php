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

use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Repository\EventRepository;
use Doctrine\ORM\Query;

readonly class EventFacade
{
    public function __construct(private EventRepository $eventRepository) {}

    public function findById(int $id): ?Event
    {
        /** @var Event|null $result */
        $result = $this->eventRepository->find($id);

        return $result;
    }

    /**
     * @return Event[]
     */
    public function listEventsByOwner(EventRepositoryDTO $eventData): array
    {
        /** @var Event[] $result */
        $result = $this->eventRepository
            ->allByOwner($eventData)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryEventsByOwner(EventRepositoryDTO $eventData): Query
    {
        return $this->eventRepository
            ->allByOwner($eventData)
            ->getQuery();
    }

    /**
     * @return Event[]
     */
    public function listByParticipant(EventRepositoryDTO $eventData): array
    {
        /** @var Event[] $result */
        $result = $this->eventRepository
            ->allByParticipant($eventData)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
