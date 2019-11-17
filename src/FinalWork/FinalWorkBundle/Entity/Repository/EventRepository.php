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

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\FinalWorkBundle\Entity\EventType;
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\SonataUserBundle\Entity\User;

class EventRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->leftJoin('event.participant', 'participant')
            ->leftJoin('event.address', 'address')
            ->setCacheable(true);
    }

    /**
     * @param Work $work
     * @return QueryBuilder
     */
    public function findAllByWork(Work $work): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('participant.work = :work')
            ->orderBy('event.start', Criteria::DESC)
            ->setParameter('work', $work);
    }

    /**
     * @param User $user
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @param EventType|null $eventType
     * @return QueryBuilder
     */
    public function findAllByOwner(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): QueryBuilder
    {
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

    /**
     * @param User $user
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @param EventType|null $eventType
     * @return QueryBuilder
     */
    public function findAllByParticipant(
        User $user,
        ?DateTime $startDate = null,
        ?DateTime $endDate = null,
        ?EventType $eventType = null
    ): QueryBuilder
    {
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
