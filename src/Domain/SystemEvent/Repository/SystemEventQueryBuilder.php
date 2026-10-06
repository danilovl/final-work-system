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

namespace App\Domain\SystemEvent\Repository;

use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class SystemEventQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinAll(): self
    {
        $this->queryBuilder
            ->leftJoin('system_event.type', 'type')
            ->leftJoin('system_event.recipient', 'recipient')
            ->leftJoin('system_event.conversation', 'conversation')
            ->leftJoin('system_event.event', 'event')
            ->leftJoin('system_event.owner', 'owner')
            ->leftJoin('system_event.task', 'task')
            ->leftJoin('system_event.work', 'work');

        return $this;
    }

    public function distinct(): self
    {
        $this->queryBuilder->distinct();

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('system_event.createdAt', $order);

        return $this;
    }

    public function whereByRecipient(User $recipient): self
    {
        $this->queryBuilder
            ->where('recipient.recipient = :recipient')
            ->setParameter('recipient', $recipient);

        return $this;
    }

    public function whereByRecipientViewed(bool $viewed): self
    {
        $this->queryBuilder
            ->andWhere('recipient.viewed = :viewed')
            ->setParameter('viewed', $viewed);

        return $this;
    }

    public function selectCount(): self
    {
        $this->queryBuilder->select('count(system_event.id)');

        return $this;
    }
}
