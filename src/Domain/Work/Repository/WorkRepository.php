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

use App\Domain\User\Entity\User;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use SortDirection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class WorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Work::class);
    }

    private function createWorkQueryBuilder(): WorkQueryBuilder
    {
        return new WorkQueryBuilder($this->createQueryBuilder('work'));
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work');
    }

    public function createSearchQueryBuilder(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->addSelect('type', 'status', 'author', 'supervisor', 'opponent', 'consultant', 'categories')
            ->join("{$alias}.type", 'type')
            ->join("{$alias}.status", 'status')
            ->join("{$alias}.author", 'author')
            ->join("{$alias}.supervisor", 'supervisor')
            ->leftJoin("{$alias}.opponent", 'opponent')
            ->leftJoin("{$alias}.consultant", 'consultant')
            ->leftJoin("{$alias}.categories", 'categories');
    }

    public function workDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createWorkQueryBuilder()
            ->selectDistinctDeadline()
            ->leftJoinSupervisor()
            ->whereBySupervisor($user)
            ->orderByDeadline(SortDirection::Descending)
            ->getQueryBuilder();
    }

    public function workProgramDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createWorkQueryBuilder()
            ->selectDistinctProgramDeadline()
            ->leftJoinSupervisor()
            ->whereBySupervisor($user)
            ->whereProgramDeadlineNotNull()
            ->orderByProgramDeadline(SortDirection::Descending)
            ->getQueryBuilder();
    }

    public function allByUserRole(
        User $user,
        string $type,
        ?WorkType $workType,
        ?WorkStatus $workStatus
    ): QueryBuilder {
        return $this->createWorkQueryBuilder()
            ->selectStatus()
            ->selectUsers()
            ->joinStatus()
            ->whereByUserRole($type, $user)
            ->whereByWorkType($workType)
            ->whereByWorkStatus($workStatus)
            ->orderById()
            ->getQueryBuilder();
    }

    public function allByUserStatus(WorkRepositoryDTO $workData): QueryBuilder
    {
        $builder = $this->createWorkQueryBuilder()
            ->selectStatus()
            ->joinStatus();

        if ($workData->supervisor !== null) {
            $builder = $builder
                ->leftJoinSupervisor()
                ->whereBySupervisorFilter($workData->supervisor);
        }

        $builder = $builder
            ->whereByUserAndType($workData->type, $workData->user)
            ->whereByWorkStatus($workData->workStatus);

        return $builder->getQueryBuilder();
    }

    /**
     * @param int[] $userIds
     * @param WorkStatus[]|null $workStatus
     */
    public function allByUsersSupervisorStatus(
        array $userIds,
        User $supervisor,
        string $type,
        ?array $workStatus
    ): QueryBuilder {
        return $this->createWorkQueryBuilder()
            ->selectStatus()
            ->selectSupervisor()
            ->joinStatus()
            ->leftJoinSupervisor()
            ->whereBySupervisorFilter($supervisor)
            ->whereByUsersAndType($type, $userIds)
            ->whereByWorkStatus($workStatus)
            ->getQueryBuilder();
    }

    public function getWorksAfterDeadline(): QueryBuilder
    {
        $callback = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder
                ->andWhere('work.deadline < CURRENT_DATE()')
                ->andWhere('status.id = :workStatusId')
                ->setParameter('workStatusId', WorkStatusConstant::ACTIVE);
        };

        return $this->createWorkQueryBuilder()
            ->joinStatus()
            ->byCallback($callback)
            ->getQueryBuilder();
    }
}
