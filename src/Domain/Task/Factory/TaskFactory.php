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

namespace App\Domain\Task\Factory;

use App\Domain\BaseModelFactory;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\TaskModel;

class TaskFactory extends BaseModelFactory
{
    public function flushFromModel(
        TaskModel $taskModel,
        Task $task = null
    ): Task {
        $task = $task ?? new Task;
        $task = $this->fromModel($task, $taskModel);

        $this->entityManagerService->persistAndFlush($task);

        return $task;
    }

    public function fromModel(
        Task $task,
        TaskModel $taskModel
    ): Task {
        $task->setName($taskModel->name);
        $task->setDescription($taskModel->description);
        $task->setComplete($taskModel->complete);
        $task->setActive($taskModel->active);
        $task->setDeadline($taskModel->deadline);
        $task->setWork($taskModel->work);
        $task->setOwner($taskModel->owner);

        return $task;
    }
}
