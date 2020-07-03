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

use DateTime;
use App\Entity\Task;
use App\Model\Traits\{
    ActiveTrait,
    SimpleInformationTrait
};
use App\Entity\Work;
use App\Entity\User;

class TaskModel
{
    use SimpleInformationTrait;
    use ActiveTrait;

    public bool $complete = false;
    public ?DateTime $deadline = null;
    public ?User $owner = null;
    public ?Work $work = null;

    public static function fromTask(Task $task): self
    {
        $model = new self;
        $model->name = $task->getName();
        $model->description = $task->getDescription();
        $model->complete = $task->isComplete();
        $model->active = $task->isActive();
        $model->deadline = $task->getDeadline();
        $model->work = $task->getWork();
        $model->owner = $task->getOwner();

        return $model;
    }
}
