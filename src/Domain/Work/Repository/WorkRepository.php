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
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
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

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work');
    }

    public function workDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work')
            ->select('DISTINCT work.deadline')
            ->leftJoin('work.supervisor', 'supervisor')
            ->where('supervisor.id = :userId')
            ->orderBy('work.deadline', Order::Descending->value)
            ->setParameter('userId', $user->getId());
    }

    public function workProgramDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work')
            ->select('work.deadlineProgram')
            ->select('DISTINCT work.deadlineProgram')
            ->leftJoin('work.supervisor', 'supervisor')
            ->where('supervisor.id = :userId')
            ->andWhere('work.deadlineProgram is NOT NULL')
            ->orderBy('work.deadlineProgram', Order::Descending->value)
            ->setParameter('userId', $user->getId());
    }

    public function allByUserStatus(WorkRepositoryDTO $workData): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('work')
            ->addSelect('status')
            ->innerJoin('work.status', 'status');

        if ($workData->user !== null) {
            $queryBuilder->setParameter('userId', $workData->user->getId());
        }

        if ($workData->supervisor !== null) {
            $queryBuilder->leftJoin('work.supervisor', 'supervisor')
                ->where('supervisor.id = :supervisorId')
                ->setParameter('supervisorId', $workData->supervisor->getId());
        }

        switch ($workData->type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $queryBuilder->leftJoin('work.author', 'author')
                    ->andWhere('author.id = :userId');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $queryBuilder->leftJoin('work.opponent', 'opponent')
                    ->andWhere('opponent.id = :userId');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $queryBuilder->leftJoin('work.consultant', 'consultant')
                    ->andWhere('consultant.id = :userId');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $queryBuilder->leftJoin('work.supervisor', 'supervisor')
                    ->andWhere('supervisor.id = :userId');

                break;
        }

        $workStatus = $workData->workStatus;
        if ($workStatus instanceof WorkStatus) {
            $queryBuilder->andWhere('status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $queryBuilder;
    }

    public function getWorksAfterDeadline(): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->join('work.status', 'status')
            ->andWhere('work.deadline < CURRENT_DATE()')
            ->andWhere('status.id = :workStatusId')
            ->setParameter('workStatusId', WorkStatusConstant::ACTIVE);
    }
}
