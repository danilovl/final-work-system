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

use App\Domain\Task\Constant\TaskStatusConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;

readonly class TaskStatusService
{
    public function __construct(private TaskEventDispatcher $taskEventDispatcher) {}

    public function changeStatus(string $type, Task $task): void
    {
        switch ($type) {
            case TaskStatusConstant::ACTIVE->value:
                $task->changeActive();

                if ($task->isActive()) {
                    $this->taskEventDispatcher->onTaskChangeStatus($task, $type);
                }

                break;
            case TaskStatusConstant::COMPLETE->value:
                $task->changeComplete();

                $this->taskEventDispatcher->onTaskChangeStatus($task, $type);

                break;
            case TaskStatusConstant::NOTIFY->value:
                if ($task->isNotifyComplete()) {
                    $task->changeNotifyComplete();

                    $this->taskEventDispatcher->onTaskChangeStatus($task, $type);
                }

                break;
        }
    }
}
