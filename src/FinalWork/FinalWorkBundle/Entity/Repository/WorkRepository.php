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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\FinalWorkBundle\Constant\WorkUserTypeConstant;
use FinalWork\FinalWorkBundle\Entity\WorkStatus;
use FinalWork\SonataUserBundle\Entity\User;

class WorkRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('work');
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function getWorkDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work')
            ->select('DISTINCT work.deadline')
            ->where('work.supervisor = :user')
            ->orderBy('work.deadline', Criteria::DESC)
            ->setParameter('user', $user);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function getWorkProgramDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work')
            ->select('work.deadlineProgram')
            ->select('DISTINCT work.deadlineProgram')
            ->where('work.supervisor = :user')
            ->andWhere('work.deadlineProgram is NOT NULL')
            ->orderBy('work.deadlineProgram', Criteria::DESC)
            ->setParameter('user', $user);
    }

    /**
     * @param User $user
     * @param User $supervisor
     * @param string $type
     * @param WorkStatus|iterable|null $workStatus
     * @return QueryBuilder
     */
    public function findAllByUserStatus(
        User $user,
        User $supervisor,
        string $type,
        $workStatus = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('work')
            ->addSelect('status')
            ->innerJoin('work.status', 'status')
            ->where('work.supervisor = :supervisor')
            ->setParameter('user', $user)
            ->setParameter('supervisor', $supervisor)
            ->setCacheable(true);

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
            $queryBuilder->andWhere('status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $queryBuilder;
    }
}
