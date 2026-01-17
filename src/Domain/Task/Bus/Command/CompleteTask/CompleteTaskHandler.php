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

namespace App\Domain\Task\Bus\Command\CompleteTask;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Task\Constant\TaskStatusConstant;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;

readonly class CompleteTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private TaskEventDispatcher $taskEventDispatcher
    ) {}

    public function __invoke(CompleteTaskCommand $command): void
    {
        $task = $command->task;

        $task->changeComplete();
        $this->entityManagerService->flush();

        $this->taskEventDispatcher->onTaskChangeStatus(
            $task,
            TaskStatusConstant::COMPLETE->value
        );
    }
}
