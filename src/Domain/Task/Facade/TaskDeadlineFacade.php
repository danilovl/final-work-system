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

use App\Domain\Task\Repository\TaskRepository;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Domain\User\Entity\User;

class TaskDeadlineFacade
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly ParameterServiceInterface $parameterService
    ) {
    }

    public function getDeadlinesByOwner(
        User $user,
        int $limit = null
    ): array {
        $limit = $limit ?? $this->parameterService->getInt('pagination.task.deadline_limit');

        $taskDeadLinesQuery = $this->taskRepository
            ->byDeadlineOwner($user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $taskDeadLines = [];
        foreach ($taskDeadLinesQuery as $taskDeadLine) {
            $taskDeadLines[] = $taskDeadLine['deadline'];
        }

        return $taskDeadLines;
    }

    public function getTasksAfterDeadline(int $offset, int $limit): array
    {
        return $this->taskRepository
            ->getTasksAfterDeadline()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
