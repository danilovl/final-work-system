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

namespace App\Domain\Task\DTO\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Model\TaskModel;
use App\Domain\Work\Entity\Work;

class TaskFormFactoryDTO
{
    public function __construct(
        public ControllerMethodConstant $type,
        public TaskModel $taskModel,
        public ?Task $task = null,
        public ?Work $work = null,
        public array $options = []
    ) {}
}
