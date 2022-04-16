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
use Doctrine\ORM\Query;

class TaskFacade
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    public function find(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    /**
     * @return Task[]
     */
    public function findAll(int $limit = null): array
    {
        return $this->taskRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function queryTasksByOwner(User $user): Query
    {
        return $this->taskRepository
            ->allByOwner($user)
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
    public function findAllByOwnerComplete(
        User $user,
        bool $isComplete
    ): array {
        return $this->taskRepository
            ->allByOwnerComplete($user, $isComplete)
            ->getQuery()
            ->getResult();
    }

    public function queryTasksByWorks(array $works): Query
    {
        return $this->taskRepository
            ->allByWorks($works)
            ->getQuery();
    }
}
