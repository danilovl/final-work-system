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

namespace App\Repository;

use App\Entity\{
    User,
    Work,
    Event,
    EventType
};
use DateTime;
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

    public function allByOwner(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): QueryBuilder {
        $queryBuilder = $this->baseQueryBuilder()
            ->where('event.owner = :owner')
            ->setParameter('owner', $user);

        if ($startDate !== null && $endDate !== null) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('event.start', ':start'),
                    $queryBuilder->expr()->lte('event.end', ':end')
                ))
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);
        }

        if ($eventType !== null) {
            $queryBuilder->andWhere('event.type = :eventType')
                ->setParameter('eventType', $eventType);
        }

        return $queryBuilder;
    }

    public function allByParticipant(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): QueryBuilder {
        $queryBuilder = $this->baseQueryBuilder()
            ->where('participant.user = :participant')
            ->groupBy('event.id')
            ->setParameter('participant', $user);

        if ($startDate !== null && $endDate !== null) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('event.start', ':start'),
                    $queryBuilder->expr()->lte('event.end', ':end')
                ))
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);
        }

        if ($eventType !== null) {
            $queryBuilder->andWhere('event.type = :eventType')
                ->setParameter('eventType', $eventType);
        }

        return $queryBuilder;
    }
}
