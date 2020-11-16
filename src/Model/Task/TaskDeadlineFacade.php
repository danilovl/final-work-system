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

namespace App\Model\Task;

use Danilovl\ParameterBundle\Services\ParameterService;
use App\Entity\User;
use App\Repository\TaskRepository;

class TaskDeadlineFacade
{
    private TaskRepository $taskRepository;
    private ParameterService $parameterService;

    public function __construct(
        TaskRepository $taskRepository,
        ParameterService $parameterService
    ) {
        $this->taskRepository = $taskRepository;
        $this->parameterService = $parameterService;
    }

    public function getDeadlinesByOwner(
        User $user,
        int $limit = null
    ): array {
        $limit = $limit ?? $this->parameterService->get('pagination.task.deadline_limit');

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
