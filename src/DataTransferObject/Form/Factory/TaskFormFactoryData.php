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

namespace App\DataTransferObject\Form\Factory;

use App\DataTransferObject\BaseDataTransferObject;
use App\Entity\{
    Work,
    Task
};
use App\Model\Task\TaskModel;

class TaskFormFactoryData extends BaseDataTransferObject
{
    public string $type;
    public TaskModel $taskModel;
    public ?Task $task = null;
    public ?Work $work = null;
    public array $options = [];
}
