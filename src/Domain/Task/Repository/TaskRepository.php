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

namespace App\Domain\Task\Repository;

use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->setCacheable(true);
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->join('task.work', 'work')
            ->where('task.owner = :user')
            ->orderBy('task.createdAt', Criteria::DESC)
            ->setParameter('user', $user);
    }

    public function allByWork(Work $work, bool $active = false): QueryBuilder
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

    public function byDeadlineOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->select('DISTINCT task.deadline')
            ->where('task.owner = :user')
            ->orderBy('task.deadline', Criteria::DESC)
            ->setParameter('user', $user);
    }

    public function allByOwnerComplete(
        User $user,
        bool $isComplete
    ): QueryBuilder {
        return $this->createQueryBuilder('task')
            ->andWhere('task.owner = :user')
            ->andWhere('task.complete = :isComplete')
            ->setParameter('user', $user)
            ->setParameter('isComplete', $isComplete);
    }

    public function countByOwnerComplete(
        User $user,
        bool $isComplete
    ): QueryBuilder {
        return $this->createQueryBuilder('task')
            ->distinct()
            ->select('count(task.id)')
            ->andWhere('task.owner = :user')
            ->andWhere('task.complete = :isComplete')
            ->setParameter('user', $user)
            ->setParameter('isComplete', $isComplete);
    }

    public function getTasksAfterDeadline(): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->andWhere('task.complete = :complete')
            ->andWhere('task.notifyComplete = :notifyComplete')
            ->andWhere('task.active = :active')
            ->andWhere('task.deadline < CURRENT_DATE()')
            ->setParameter('active', true)
            ->setParameter('complete', false)
            ->setParameter('notifyComplete', false);
    }

    public function allByWorks(array $works, bool $active = false): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->join('task.work', 'work')
            ->where('work IN (:works)')
            ->orderBy('task.deadline', Criteria::DESC)
            ->orderBy('task.createdAt', Criteria::DESC)
            ->setParameter('works', $works);

        if ($active === true) {
            $queryBuilder->andWhere('task.active = :active')
                ->setParameter('active', $active);
        }

        return $queryBuilder;
    }
}
