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

namespace App\Model\WorkStatus\Repository;

use App\Constant\WorkUserTypeConstant;
use App\DataTransferObject\Repository\WorkStatusData;
use App\Model\WorkStatus\Entity\WorkStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class WorkStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkStatus::class);
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work_status');
    }

    public function countByUser(WorkStatusData $workStatusData): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('work_status')
            ->select('work_status.name, COUNT(work.id) as count')
            ->leftJoin('work_status.works', 'work')
            ->leftJoin('work.supervisor', 'supervisor')
            ->where('supervisor = :supervisor')
            ->setParameter('user', $workStatusData->user)
            ->setParameter('supervisor', $workStatusData->supervisor)
            ->groupBy('work_status.name');

        switch ($workStatusData->type) {
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

        $workStatus = $workStatusData->workStatus;
        if ($workStatus instanceof WorkStatus) {
            $queryBuilder->andWhere('work_status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('work_status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $queryBuilder;
    }
}
