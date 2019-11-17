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

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\FinalWorkBundle\Constant\WorkUserTypeConstant;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\WorkStatus;

class WorkStatusRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work_status');
    }

    /**
     * @param User $user
     * @param User $supervisor
     * @param string $type
     * @param WorkStatus|iterable $workStatus
     * @return QueryBuilder
     */
    public function getCountByUser(
        User $user,
        User $supervisor,
        string $type,
        $workStatus
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('work_status')
            ->select('work_status.name, COUNT(work.id) as count')
            ->leftJoin('work_status.works', 'work')
            ->leftJoin('work.supervisor', 'supervisor')
            ->where('supervisor = :supervisor')
            ->setParameter('user', $user)
            ->setParameter('supervisor', $supervisor)
            ->groupBy('work_status.name');

        switch ($type) {
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
