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

namespace App\Domain\Task\Repository;

use App\Application\Repository\BaseQueryBuilder;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Common\Collections\Order;

class TaskQueryBuilder extends BaseQueryBuilder
{
    public function selectWork(): self
    {
        $this->queryBuilder->addSelect('work');

        return $this;
    }

    public function selectWorkAuthor(): self
    {
        $this->queryBuilder->addSelect('author');

        return $this;
    }

    public function joinWork(): self
    {
        $this->queryBuilder->join('task.work', 'work');

        return $this;
    }

    public function joinWorkAuthor(): self
    {
        $this->queryBuilder->join('work.author', 'author');

        return $this;
    }

    public function joinStatus(): self
    {
        $this->queryBuilder->join('task.status', 'status');

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('task.createdAt', $order);

        return $this;
    }

    public function orderByDeadline(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('task.deadline', $order);

        return $this;
    }

    public function byWork(Work $work): self
    {
        $this->queryBuilder
            ->andWhere('task.work = :work')
            ->setParameter('work', $work);

        return $this;
    }

    /**
     * @param Work[] $works
     */
    public function byWorks(array $works): self
    {
        $this->queryBuilder
            ->andWhere('work IN (:works)')
            ->setParameter('works', $works);

        return $this;
    }

    public function byOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('task.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function byActive(bool $active): self
    {
        $this->queryBuilder
            ->andWhere('task.active = :active')
            ->setParameter('active', $active);

        return $this;
    }

    public function byComplete(bool $isComplete): self
    {
        $this->queryBuilder
            ->andWhere('task.complete = :isComplete')
            ->setParameter('isComplete', $isComplete);

        return $this;
    }

    public function byNotifyComplete(bool $isComplete): self
    {
        $this->queryBuilder
            ->andWhere('task.notifyComplete = :notifyComplete')
            ->setParameter('notifyComplete', $isComplete);

        return $this;
    }

    public function byIds(array $ids): self
    {
        $this->queryBuilder
            ->andWhere('task.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $this;
    }
}
