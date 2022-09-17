<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\Event\Repository;

use App\Domain\Event\DataTransferObject\EventRepositoryData;
use App\Domain\Event\Entity\Event;
use App\Domain\Work\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->leftJoin('event.participant', 'participant')
            ->leftJoin('event.address', 'address')
            ->setCacheable(true);
    }

    public function allByWork(Work $work): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('participant.work = :work')
            ->orderBy('event.start', Criteria::DESC)
            ->setParameter('work', $work);
    }

    public function allByOwner(EventRepositoryData $eventData): QueryBuilder
    {
        $queryBuilder = $this->baseQueryBuilder()
            ->where('event.owner = :owner')
            ->setParameter('owner', $eventData->user);

        $this->filterByBetweenDate($queryBuilder, $eventData);
        $this->filterByEventType($queryBuilder, $eventData);

        return $queryBuilder;
    }

    public function allByParticipant(EventRepositoryData $eventData): QueryBuilder
    {
        $queryBuilder = $this->baseQueryBuilder()
            ->where('participant.user = :participant')
            ->groupBy('event.id')
            ->setParameter('participant', $eventData->user);

        $this->filterByBetweenDate($queryBuilder, $eventData);
        $this->filterByEventType($queryBuilder, $eventData);

        return $queryBuilder;
    }

    private function filterByBetweenDate(QueryBuilder $queryBuilder, EventRepositoryData $eventData): void
    {
        if ($eventData->startDate === null || $eventData->endDate === null) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('event.start', ':start'),
                $queryBuilder->expr()->lte('event.end', ':end')
            ))
            ->setParameter('start', $eventData->startDate)
            ->setParameter('end', $eventData->endDate);
    }

    private function filterByEventType(QueryBuilder $queryBuilder, EventRepositoryData $eventData): void
    {
        if ($eventData->eventType === null) {
            return;
        }

        $queryBuilder->andWhere('event.type = :eventType')
            ->setParameter('eventType', $eventData->eventType);
    }
}
