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

use App\Domain\Task\DTO\Api\Input\TaskInput;
use App\Domain\Task\Model\TaskModel;
use App\Domain\User\Entity\User;
use App\Domain\Work\Facade\WorkFacade;
use DateTimeImmutable;

readonly class TaskModelFactory
{
    public function __construct(private WorkFacade $workFacade) {}

    public function fromTaskInput(TaskInput $input, User $owner): TaskModel
    {
        $taskModel = new TaskModel;
        $taskModel->name = $input->name;
        $taskModel->description = $input->description;
        $taskModel->complete = $input->complete;
        $taskModel->active = $input->active;
        $taskModel->deadline = new DateTimeImmutable($input->deadline);
        $taskModel->work = $this->workFacade->getById($input->workId);
        $taskModel->owner = $owner;

        return $taskModel;
    }
}
