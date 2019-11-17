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

namespace FinalWork\FinalWorkBundle\Model\Task;

use DateTime;
use FinalWork\FinalWorkBundle\Entity\Task;
use FinalWork\FinalWorkBundle\Model\Traits\{
    ActiveTrait,
    SimpleInformationTrait
};
use Symfony\Component\Validator\Constraints as Assert;

class TaskModel
{
    use SimpleInformationTrait;
    use ActiveTrait;

    /**
     * @var bool
     */
    public $complete = false;

    /**
     * @var DateTime
     * @Assert\DateTime()
     */
    public $deadline;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @Assert\NotBlank()
     */
    public $work;

    /**
     * @param Task $task
     * @return TaskModel
     */
    public static function fromTask(Task $task): self
    {
        $model = new self();
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
