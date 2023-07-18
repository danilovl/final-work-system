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

namespace App\Domain\Task\Model;

use App\Application\Traits\Model\{
    SimpleInformationTrait};
use App\Application\Traits\Model\ActiveTrait;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class TaskModel
{
    use SimpleInformationTrait;
    use ActiveTrait;

    public bool $complete = false;
    public ?DateTime $deadline = null;
    public ?User $owner = null;
    public ?Work $work = null;
    public iterable|ArrayCollection|null $works = null;

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
