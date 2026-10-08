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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
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

    public function workDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createWorkQueryBuilder()
            ->selectDistinctDeadline()
            ->leftJoinSupervisor()
            ->whereBySupervisor($user)
            ->orderByDeadline(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function workProgramDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createWorkQueryBuilder()
            ->selectDistinctProgramDeadline()
            ->leftJoinSupervisor()
            ->whereBySupervisor($user)
            ->whereProgramDeadlineNotNull()
            ->orderByProgramDeadline(Order::Descending->value)
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
