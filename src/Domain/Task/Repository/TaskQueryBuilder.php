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

namespace App\Domain\Task\Repository;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;
use Webmozart\Assert\Assert;

class TaskQueryBuilder extends BaseQueryBuilder
{
    public function selectWork(): self
    {
        $this->queryBuilder->addSelect('work');

        return $this;
    }

    public function selectWorkStatus(): self
    {
        $this->queryBuilder->addSelect('work_status');

        return $this;
    }

    public function selectWorkType(): self
    {
        $this->queryBuilder->addSelect('work_type');

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

    public function joinWorkStatus(): self
    {
        $this->queryBuilder->join('work.status', 'work_status');

        return $this;
    }

    public function joinWorkTypes(): self
    {
        $this->queryBuilder->join('work.type', 'work_type');

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

    public function whereByWork(Work $work): self
    {
        $this->queryBuilder
            ->andWhere('task.work = :work')
            ->setParameter('work', $work);

        return $this;
    }

    /**
     * @param Work[] $works
     */
    public function whereByWorks(array $works): self
    {
        Assert::allIsInstanceOf($works, Work::class);

        $this->queryBuilder
            ->andWhere('work IN (:works)')
            ->setParameter('works', $works);

        return $this;
    }

    public function whereByOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('task.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function whereByActive(bool $active): self
    {
        $this->queryBuilder
            ->andWhere('task.active = :active')
            ->setParameter('active', $active);

        return $this;
    }

    public function whereByComplete(bool $isComplete): self
    {
        $this->queryBuilder
            ->andWhere('task.complete = :isComplete')
            ->setParameter('isComplete', $isComplete);

        return $this;
    }

    public function whereByNotifyComplete(bool $isComplete): self
    {
        $this->queryBuilder
            ->andWhere('task.notifyComplete = :notifyComplete')
            ->setParameter('notifyComplete', $isComplete);

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function whereByIds(array $ids): self
    {
        Assert::allInteger($ids);

        $this->queryBuilder
            ->andWhere('task.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $this;
    }
}
