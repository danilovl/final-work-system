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
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    private function createTaskQueryBuilder(): TaskQueryBuilder
    {
        return new TaskQueryBuilder($this->createQueryBuilder('task'));
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('task');
    }

    public function allByOwner(User $user): QueryBuilder
    {
        return $this->createTaskQueryBuilder()
            ->selectWork()
            ->selectWorkAuthor()
            ->joinWork()
            ->joinWorkAuthor()
            ->byOwner($user)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    /**
     * @param int[] $ids
     */
    public function getByIds(array $ids): QueryBuilder
    {
        Assert::allInteger($ids);

        return $this->createTaskQueryBuilder()
            ->joinWork()
            ->byIds($ids)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function allByWork(Work $work, bool $active = false): QueryBuilder
    {
        $queryBuilder = $this->createTaskQueryBuilder()
            ->joinWork()
            ->byWork($work);

        if ($active === true) {
            $queryBuilder = $queryBuilder->byActive($active);
        }

        return $queryBuilder->getQueryBuilder();
    }

    public function byDeadlineOwner(User $user): QueryBuilder
    {
        $callback = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->select('DISTINCT task.deadline');
        };

        return $this->createTaskQueryBuilder()
            ->byOwner($user)
            ->orderByDeadline()
            ->byCallback($callback)
            ->getQueryBuilder();
    }

    public function allByOwnerComplete(User $user, bool $isComplete): QueryBuilder
    {
        return $this->createTaskQueryBuilder()
            ->byOwner($user)
            ->byComplete($isComplete)
            ->getQueryBuilder();
    }

    public function countByOwnerComplete(User $user, bool $isComplete): QueryBuilder
    {
        $callback = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder
                ->select('count(task.id)')
                ->andWhere('task.owner = :user');
        };

        return $this->createTaskQueryBuilder()
            ->byOwner($user)
            ->byComplete($isComplete)
            ->byCallback($callback)
            ->getQueryBuilder();
    }

    public function getTasksAfterDeadline(): QueryBuilder
    {
        $callback = static function (QueryBuilder $queryBuilder): void {
            $queryBuilder
                ->andWhere('task.deadline < CURRENT_DATE()')
                ->andWhere('status.id = :workStatusId')
                ->setParameter('workStatusId', WorkStatusConstant::ACTIVE);
        };

        return $this->createTaskQueryBuilder()
            ->joinWork()
            ->joinStatus()
            ->byActive(true)
            ->byComplete(false)
            ->byNotifyComplete(false)
            ->byCallback($callback)
            ->getQueryBuilder();
    }

    /**
     * @param Work[] $works
     */
    public function allByWorks(array $works, bool $active = false): QueryBuilder
    {
        Assert::allIsInstanceOf($works, Work::class);

        $queryBuilder = $this->createTaskQueryBuilder()
            ->selectWork()
            ->joinWork()
            ->byWorks($works)
            ->orderByCreatedAt()
            ->orderByDeadline();

        if ($active === true) {
            $queryBuilder = $queryBuilder->byActive($active);
        }

        return $queryBuilder->getQueryBuilder();
    }
}
