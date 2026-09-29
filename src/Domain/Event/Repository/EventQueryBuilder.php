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

use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use DateTimeInterface;
use Doctrine\Common\Collections\Order;

class EventQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinParticipantWork(): self
    {
        $this->queryBuilder->leftJoin('participant.work', 'work');

        return $this;
    }

    public function leftJoinParticipantUser(): self
    {
        $this->queryBuilder->leftJoin('participant.user', 'user');

        return $this;
    }

    public function selectParticipantWorkAddressUser(): self
    {
        $this->queryBuilder->addSelect('participant, work, address, user');

        return $this;
    }

    public function whereByParticipantWork(Work $work): self
    {
        $this->queryBuilder
            ->andWhere('participant.work = :work')
            ->setParameter('work', $work);

        return $this;
    }

    public function whereByOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('event.owner = :owner')
            ->setParameter('owner', $user);

        return $this;
    }

    public function whereByParticipantUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('participant.user = :participant')
            ->setParameter('participant', $user);

        return $this;
    }

    public function orderByStart(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('event.start', $order);

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('event.createdAt', $order);

        return $this;
    }

    public function groupByEventId(): self
    {
        $this->queryBuilder->groupBy('event.id');

        return $this;
    }

    public function whereByBetweenDate(DateTimeInterface $start, DateTimeInterface $end): self
    {
        $this->queryBuilder
            ->andWhere($this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->gte('event.start', ':start'),
                $this->queryBuilder->expr()->lte('event.end', ':end')
            ))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $this;
    }

    public function whereByEventType(EventType $eventType): self
    {
        $this->queryBuilder
            ->andWhere('event.type = :eventType')
            ->setParameter('eventType', $eventType);

        return $this;
    }
}
