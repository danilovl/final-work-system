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

namespace App\Domain\Task\Facade;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepository;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\ORM\Query;
use Webmozart\Assert\Assert;

readonly class TaskFacade
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function findById(int $id): ?Task
    {
        /** @var Task|null $result */
        $result = $this->taskRepository->find($id);

        return $result;
    }

    /**
     * @return Task[]
     */
    public function list(?int $limit = null): array
    {
        /** @var Task[] $result */
        $result = $this->taskRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryByOwner(User $user): Query
    {
        return $this->taskRepository
            ->allByOwner($user)
            ->getQuery();
    }

    /**
     * @param int[] $ids
     */
    public function queryByIds(array $ids): Query
    {
        Assert::allInteger($ids);

        return $this->taskRepository
            ->getByIds($ids)
            ->getQuery();
    }

    public function getTotalCompleteByOwner(
        User $user,
        bool $isComplete
    ): int {
        return (int) $this->taskRepository
            ->countByOwnerComplete($user, $isComplete)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isTasksCompleteByOwner(
        User $user,
        bool $isComplete
    ): bool {
        return $this->getTotalCompleteByOwner($user, $isComplete) > 0;
    }

    /**
     * @return Task[]
     */
    public function listByOwnerComplete(
        User $user,
        bool $isComplete
    ): array {
        /** @var Task[] $result */
        $result = $this->taskRepository
            ->allByOwnerComplete($user, $isComplete)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param Work[] $works
     */
    public function queryByWorks(array $works): Query
    {
        Assert::allIsInstanceOf($works, Work::class);

        return $this->taskRepository
            ->allByWorks($works)
            ->getQuery();
    }
}
