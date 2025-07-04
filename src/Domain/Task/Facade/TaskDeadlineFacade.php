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
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Domain\User\Entity\User;
use Webmozart\Assert\Assert;

readonly class TaskDeadlineFacade
{
    public function __construct(
        private TaskRepository $taskRepository,
        private ParameterServiceInterface $parameterService
    ) {}

    public function listByOwner(
        User $user,
        ?int $limit = null
    ): array {
        $limit ??= $this->parameterService->getInt('pagination.task.deadline_limit');

        /** @var array<array{deadline: string}> $taskDeadLinesQuery */
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

    /**
     * @return Task[]
     */
    public function listAfterDeadline(int $offset, int $limit): array
    {
        /** @var array $result */
        $result = $this->taskRepository
            ->getTasksAfterDeadline()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Task::class);

        return $result;
    }
}
