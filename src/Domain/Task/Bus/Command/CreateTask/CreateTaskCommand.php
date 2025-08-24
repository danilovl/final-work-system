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

namespace App\Domain\Task\Bus\Command\CreateTask;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Task\Model\TaskModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateTaskCommand implements CommandInterface
{
    private function __construct(public TaskModel $taskModel) {}

    public static function create(TaskModel $taskModel): self
    {
        return new self($taskModel);
    }
}
