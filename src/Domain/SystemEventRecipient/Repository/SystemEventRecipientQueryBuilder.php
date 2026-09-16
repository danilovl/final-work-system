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

namespace App\Domain\SystemEventRecipient\Repository;

use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class SystemEventRecipientQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinAll(): self
    {
        $this->queryBuilder
            ->leftJoin('system_event_recipient.systemEvent', 'systemEvent')
            ->leftJoin('systemEvent.type', 'type')
            ->leftJoin('systemEvent.conversation', 'conversation')
            ->leftJoin('systemEvent.event', 'event')
            ->leftJoin('systemEvent.owner', 'owner')
            ->leftJoin('systemEvent.task', 'task')
            ->leftJoin('systemEvent.work', 'work');

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('systemEvent.createdAt', $order);

        return $this;
    }

    public function byRecipient(User $recipient): self
    {
        $this->queryBuilder
            ->where('system_event_recipient.recipient = :recipient')
            ->setParameter('recipient', $recipient);

        return $this;
    }

    public function byViewed(bool $viewed): self
    {
        $this->queryBuilder
            ->andWhere('system_event_recipient.viewed = :viewed')
            ->setParameter('viewed', $viewed);

        return $this;
    }

    public function paginate(?int $limit, ?int $offset): self
    {
        if ($limit !== null) {
            $this->queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $this->queryBuilder->setFirstResult($offset);
        }

        return $this;
    }
}
