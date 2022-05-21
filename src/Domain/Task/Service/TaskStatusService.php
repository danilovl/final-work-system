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

namespace App\Domain\Task\Service;

use App\Application\Constant\TaskStatusConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;

class TaskStatusService
{
    public function __construct(private readonly TaskEventDispatcherService $taskEventDispatcherService)
    {
    }

    public function changeStatus(string $type, Task $task): void
    {
        switch ($type) {
            case TaskStatusConstant::ACTIVE:
                $task->changeActive();

                if ($task->isActive()) {
                    $this->taskEventDispatcherService->onTaskChangeStatus($task, $type);
                }

                break;
            case TaskStatusConstant::COMPLETE:
                $task->changeComplete();

                $this->taskEventDispatcherService->onTaskChangeStatus($task, $type);

                break;
            case TaskStatusConstant::NOTIFY:
                if ($task->isNotifyComplete()) {
                    $task->changeNotifyComplete();

                    $this->taskEventDispatcherService->onTaskChangeStatus($task, $type);
                }

                break;
        }
    }
}
