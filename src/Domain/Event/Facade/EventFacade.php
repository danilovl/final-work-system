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

use App\Domain\Event\DataTransferObject\EventRepositoryData;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Repository\EventRepository;
use Doctrine\ORM\Query;
use Webmozart\Assert\Assert;

readonly class EventFacade
{
    public function __construct(private EventRepository $eventRepository) {}

    public function find(int $id): ?Event
    {
        /** @var Event|null $result */
        $result = $this->eventRepository->find($id);

        return $result;
    }

    /**
     * @return Event[]
     */
    public function getEventsByOwner(EventRepositoryData $eventData): array
    {
        /** @var array $result */
        $result = $this->eventRepository
            ->allByOwner($eventData)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Event::class);

        return $result;
    }

    public function getEventsByOwnerQuery(EventRepositoryData $eventData): Query
    {
        return $this->eventRepository
            ->allByOwner($eventData)
            ->getQuery();
    }

    /**
     * @return Event[]
     */
    public function getEventsByParticipant(EventRepositoryData $eventData): array
    {
        /** @var array $result */
        $result = $this->eventRepository
            ->allByParticipant($eventData)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Event::class);

        return $result;
    }
}
