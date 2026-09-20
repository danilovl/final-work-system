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

use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;

class WorkStatusQueryBuilder extends BaseQueryBuilder
{
    public function selectNameAndCount(): self
    {
        $this->queryBuilder->select('work_status.name, COUNT(work.id) as count');

        return $this;
    }

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

    public function byUserAndType(?string $type, mixed $user): self
    {
        if ($type === null || $user === null) {
            return $this;
        }

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->queryBuilder
                    ->leftJoin('work.author', 'author')
                    ->andWhere('author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->queryBuilder
                    ->leftJoin('work.opponent', 'opponent')
                    ->andWhere('opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->queryBuilder
                    ->leftJoin('work.consultant', 'consultant')
                    ->andWhere('consultant = :user');

                break;
            default:
                return $this;
        }

        $this->queryBuilder->setParameter('user', $user);

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

    public function groupByName(): self
    {
        $this->queryBuilder->groupBy('work_status.name');

        return $this;
    }
}
