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

use App\Domain\User\Entity\User;
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

    /**
     * @param int[] $userIds
     * @param WorkStatus[]|null $workStatus
     */
    public function countByUsers(
        array $userIds,
        User $supervisor,
        string $type,
        ?array $workStatus
    ): QueryBuilder {
        return $this->createWorkStatusQueryBuilder()
            ->selectUserIdNameAndCount($type)
            ->leftJoinWorks()
            ->leftJoinSupervisor()
            ->bySupervisor($supervisor)
            ->byUsersAndType($type, $userIds)
            ->byWorkStatusRoot($workStatus)
            ->groupByUserIdAndName()
            ->getQueryBuilder();
    }
}
