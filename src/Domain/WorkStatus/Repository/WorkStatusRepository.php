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

use App\Domain\WorkStatus\DTO\Repository\WorkStatusRepositoryDTO;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class WorkStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkStatus::class);
    }

    private function createWorkStatusQueryBuilder(): WorkStatusQueryBuilder
    {
        return new WorkStatusQueryBuilder($this->createQueryBuilder('work_status'));
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work_status');
    }

    public function countByUser(WorkStatusRepositoryDTO $workStatusData): QueryBuilder
    {
        return $this->createWorkStatusQueryBuilder()
            ->selectNameAndCount()
            ->leftJoinWorks()
            ->leftJoinSupervisor()
            ->bySupervisor($workStatusData->supervisor)
            ->byUserAndType($workStatusData->type, $workStatusData->user)
            ->byWorkStatusRoot($workStatusData->workStatus)
            ->groupByName()
            ->getQueryBuilder();
    }
}
