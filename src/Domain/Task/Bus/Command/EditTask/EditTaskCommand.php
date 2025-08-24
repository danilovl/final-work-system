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

namespace App\Domain\Task\Bus\Command\EditTask;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Model\TaskModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditTaskCommand implements CommandInterface
{
    private function __construct(public TaskModel $taskModel, public Task $task) {}

    public static function create(TaskModel $taskModel, Task $task): self
    {
        return new self($taskModel, $task);
    }
}
