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

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Domain\Task\Factory\TaskFactory;

readonly class CreateTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskFactory $taskFactory,
        private TaskEventDispatcher $taskEventDispatcher
    ) {}

    public function __invoke(CreateTaskCommand $command): void
    {
        $taskModel = $command->taskModel;

        $task = $this->taskFactory->flushFromModel($taskModel);
        $this->taskEventDispatcher->onTaskCreate($task);
    }
}
