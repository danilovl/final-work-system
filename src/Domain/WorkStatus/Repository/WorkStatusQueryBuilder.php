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

namespace App\Domain\WorkStatus\Repository;

use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class WorkStatusQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinWorks(): self
    {
        $this->queryBuilder->leftJoin('work_status.works', 'work');

        return $this;
    }

    public function leftJoinSupervisor(): self
    {
        $this->queryBuilder->leftJoin('work.supervisor', 'supervisor');

        return $this;
    }

    public function bySupervisor(mixed $supervisor): self
    {
        $this->queryBuilder
            ->andWhere('supervisor = :supervisor')
            ->setParameter('supervisor', $supervisor);

        return $this;
    }

    public function byWorkStatusRoot(WorkStatus|iterable|null $workStatus): self
    {
        if ($workStatus instanceof WorkStatus) {
            $this->queryBuilder
                ->andWhere('work_status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work_status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $this;
    }

    public function selectUserIdNameAndCount(string $type): self
    {
        $this->queryBuilder->select(sprintf('IDENTITY(work.%s) AS userId, work_status.name, COUNT(work.id) as count', $type));

        return $this;
    }

    /**
     * @param int[] $userIds
     */
    public function byUsersAndType(string $type, array $userIds): self
    {
        $this->queryBuilder
            ->leftJoin('work.' . $type, $type)
            ->andWhere($this->queryBuilder->expr()->in($type . '.id', ':userIds'))
            ->setParameter('userIds', $userIds);

        return $this;
    }

    public function groupByUserIdAndName(): self
    {
        $this->queryBuilder
            ->groupBy('userId')
            ->addGroupBy('work_status.name');

        return $this;
    }

    public function orderByName(): self
    {
        $this->queryBuilder
            ->orderBy('work_status.name', SortDirection::Ascending)
            ->addOrderBy('work_status.id', SortDirection::Ascending);

        return $this;
    }
}
