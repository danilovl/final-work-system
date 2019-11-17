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
use FinalWork\FinalWorkBundle\Entity\Work;
use Sonata\UserBundle\Model\User;

class TaskRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->setCacheable(true);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->innerJoin('task.work', 'work')
            ->where('task.owner = :user')
            ->orderBy('task.createdAt', Criteria::DESC)
            ->setParameter('user', $user);
    }

    /**
     * @param Work $work
     * @param bool $active
     * @return QueryBuilder
     */
    public function findAllByWork(Work $work, bool $active = false): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->where('task.work = :work')
            ->orderBy('task.deadline', Criteria::DESC)
            ->orderBy('task.createdAt', Criteria::DESC)
            ->setParameter('work', $work);

        if ($active === true) {
            $queryBuilder->andWhere('task.active = :active')
                ->setParameter('active', $active);
        }

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function getTaskDeadlineByOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->select('DISTINCT task.deadline')
            ->where('task.owner = :user')
            ->orderBy('task.deadline', Criteria::DESC)
            ->setParameter('user', $user);
    }
}
