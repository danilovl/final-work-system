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

namespace App\Application\DataTransferObject\Form\Factory;

use App\Application\DataTransferObject\BaseDataTransferObject;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\TaskModel;
use App\Domain\Work\Entity\Work;

class TaskFormFactoryData extends BaseDataTransferObject
{
    public string $type;
    public TaskModel $taskModel;
    public ?Task $task = null;
    public ?Work $work = null;
    public array $options = [];
}
