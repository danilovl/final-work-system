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

namespace App\Domain\Work\Repository;

use InvalidArgumentException;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class WorkQueryBuilder extends BaseQueryBuilder
{
    public function selectStatus(): self
    {
        $this->queryBuilder->addSelect('status');

        return $this;
    }

    public function selectDistinctDeadline(): self
    {
        $this->queryBuilder->select('DISTINCT work.deadline');

        return $this;
    }

    public function selectDistinctProgramDeadline(): self
    {
        $this->queryBuilder->select('DISTINCT work.deadlineProgram');

        return $this;
    }

    public function joinStatus(): self
    {
        $this->queryBuilder->join('work.status', 'status');

        return $this;
    }

    public function selectSupervisor(): self
    {
        $this->queryBuilder->addSelect('supervisor');

        return $this;
    }

    public function leftJoinSupervisor(): self
    {
        $this->queryBuilder->leftJoin('work.supervisor', 'supervisor');

        return $this;
    }

    public function leftJoinAuthor(): self
    {
        $this->queryBuilder->leftJoin('work.author', 'author');

        return $this;
    }

    public function leftJoinOpponent(): self
    {
        $this->queryBuilder->leftJoin('work.opponent', 'opponent');

        return $this;
    }

    public function leftJoinConsultant(): self
    {
        $this->queryBuilder->leftJoin('work.consultant', 'consultant');

        return $this;
    }

    public function whereBySupervisor(User $user): self
    {
        $this->queryBuilder
            ->andWhere('supervisor.id = :userId')
            ->setParameter('userId', $user->getId());

        return $this;
    }

    public function whereBySupervisorFilter(User $supervisor): self
    {
        $this->queryBuilder
            ->andWhere('supervisor.id = :supervisorId')
            ->setParameter('supervisorId', $supervisor->getId());

        return $this;
    }

    public function whereByUserAndType(?string $type, ?User $user): self
    {
        if ($type === null || $user === null) {
            return $this;
        }

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->leftJoinAuthor();
                $this->queryBuilder->andWhere('author.id = :userId');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->leftJoinOpponent();
                $this->queryBuilder->andWhere('opponent.id = :userId');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->leftJoinConsultant();
                $this->queryBuilder->andWhere('consultant.id = :userId');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $this->leftJoinSupervisor();
                $this->queryBuilder->andWhere('supervisor.id = :userId');

                break;
            default:
                return $this;
        }

        $this->queryBuilder->setParameter('userId', $user->getId());

        return $this;
    }

    /**
     * @param int[] $userIds
     */
    public function whereByUsersAndType(string $type, array $userIds): self
    {
        $alias = match ($type) {
            WorkUserTypeConstant::AUTHOR->value => 'author',
            WorkUserTypeConstant::OPPONENT->value => 'opponent',
            WorkUserTypeConstant::CONSULTANT->value => 'consultant',
            default => throw new InvalidArgumentException('Unsupported work user type.')
        };

        $this->queryBuilder
            ->addSelect($alias)
            ->leftJoin('work.' . $alias, $alias)
            ->andWhere($this->queryBuilder->expr()->in($alias . '.id', ':userIds'))
            ->setParameter('userIds', $userIds);

        return $this;
    }

    public function whereByWorkStatus(WorkStatus|iterable|null $workStatus): self
    {
        if ($workStatus instanceof WorkStatus) {
            $this->queryBuilder
                ->andWhere('status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $this;
    }

    public function selectUsers(): self
    {
        $this->queryBuilder
            ->addSelect('author', 'supervisor', 'opponent', 'consultant')
            ->join('work.author', 'author')
            ->join('work.supervisor', 'supervisor')
            ->leftJoin('work.opponent', 'opponent')
            ->leftJoin('work.consultant', 'consultant');

        return $this;
    }

    public function whereByUserRole(string $type, User $user): self
    {
        $alias = match ($type) {
            WorkUserTypeConstant::AUTHOR->value => 'author',
            WorkUserTypeConstant::SUPERVISOR->value => 'supervisor',
            WorkUserTypeConstant::OPPONENT->value => 'opponent',
            WorkUserTypeConstant::CONSULTANT->value => 'consultant',
            default => throw new InvalidArgumentException('Unsupported work user type.')
        };

        $condition = sprintf('%s = :user', $alias);

        $this->queryBuilder
            ->andWhere($condition)
            ->setParameter('user', $user);

        return $this;
    }

    public function whereByWorkType(?WorkType $workType): self
    {
        if ($workType === null) {
            return $this;
        }

        $this->queryBuilder
            ->andWhere('work.type = :workType')
            ->setParameter('workType', $workType);

        return $this;
    }

    public function orderById(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder->orderBy('work.id', $order);

        return $this;
    }

    public function whereProgramDeadlineNotNull(): self
    {
        $this->queryBuilder->andWhere('work.deadlineProgram is NOT NULL');

        return $this;
    }

    public function orderByDeadline(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder
            ->orderBy('work.deadline', $order)
            ->addOrderBy('work.id', $order);

        return $this;
    }

    public function orderByProgramDeadline(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder
            ->orderBy('work.deadlineProgram', $order)
            ->addOrderBy('work.id', $order);

        return $this;
    }
}
