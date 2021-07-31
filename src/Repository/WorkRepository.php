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

namespace App\Repository;

use App\Constant\WorkUserTypeConstant;
use App\DataTransferObject\Repository\WorkData;
use App\Entity\{
    User,
    Work,
    WorkStatus
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

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
            ->where('work.supervisor = :user')
            ->orderBy('work.deadline', Criteria::DESC)
            ->setParameter('user', $user);
    }

    public function workProgramDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work')
            ->select('work.deadlineProgram')
            ->select('DISTINCT work.deadlineProgram')
            ->where('work.supervisor = :user')
            ->andWhere('work.deadlineProgram is NOT NULL')
            ->orderBy('work.deadlineProgram', Criteria::DESC)
            ->setParameter('user', $user);
    }

    public function allByUserStatus(WorkData $workData): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('work')
            ->addSelect('status')
            ->innerJoin('work.status', 'status')
            ->setCacheable(true);

        if ($workData->user !== null) {
            $queryBuilder->setParameter('user', $workData->user);
        }

        if ($workData->supervisor !== null) {
            $queryBuilder->where('work.supervisor = :supervisor')
                ->setParameter('supervisor', $workData->supervisor);
        }

        switch ($workData->type) {
            case WorkUserTypeConstant::AUTHOR:
                $queryBuilder->leftJoin('work.author', 'author')
                    ->andWhere('author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT:
                $queryBuilder->leftJoin('work.opponent', 'opponent')
                    ->andWhere('opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT:
                $queryBuilder->leftJoin('work.consultant', 'consultant')
                    ->andWhere('consultant = :user');

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
}
