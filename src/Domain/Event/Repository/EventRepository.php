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

namespace App\Domain\Event\Repository;

use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Work\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function createEventQueryBuilder(): EventQueryBuilder
    {
        return new EventQueryBuilder($this->baseQueryBuilder());
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->leftJoin('event.participant', 'participant')
            ->leftJoin('event.address', 'address');
    }

    public function allByWork(Work $work): QueryBuilder
    {
        return $this->createEventQueryBuilder()
            ->whereByParticipantWork($work)
            ->orderByStart(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function allByOwner(EventRepositoryDTO $eventData): QueryBuilder
    {
        $builder = $this->createEventQueryBuilder()
            ->selectParticipantWorkAddressUser()
            ->leftJoinParticipantWork()
            ->leftJoinParticipantUser()
            ->whereByOwner($eventData->getUserNotNull())
            ->orderByCreatedAt(Order::Descending->value);

        if ($eventData->startDate !== null && $eventData->endDate !== null) {
            $builder = $builder->whereByBetweenDate($eventData->startDate, $eventData->endDate);
        }

        if ($eventData->eventType !== null) {
            $builder = $builder->whereByEventType($eventData->eventType);
        }

        return $builder->getQueryBuilder();
    }

    public function allByParticipant(EventRepositoryDTO $eventData): QueryBuilder
    {
        $builder = $this->createEventQueryBuilder()
            ->whereByParticipantUser($eventData->getUserNotNull())
            ->groupByEventId();

        if ($eventData->startDate !== null && $eventData->endDate !== null) {
            $builder = $builder->whereByBetweenDate($eventData->startDate, $eventData->endDate);
        }

        if ($eventData->eventType !== null) {
            $builder = $builder->whereByEventType($eventData->eventType);
        }

        return $builder->getQueryBuilder();
    }
}
