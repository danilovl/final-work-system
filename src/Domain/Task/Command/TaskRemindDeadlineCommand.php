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

namespace App\Domain\Task\Command;

use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Domain\Task\Facade\TaskDeadlineFacade;
use App\Domain\Task\Provider\TaskRemindProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task-remind-deadline',
    description: 'Create reminder notification emails for tasks'
)]
final class TaskRemindDeadlineCommand
{
    final public const string COMMAND_NAME = 'app:task-remind-deadline';

    private const int LIMIT = 500;

    public function __construct(
        private readonly TaskEventDispatcher $taskEventDispatcher,
        private readonly TaskDeadlineFacade $taskDeadlineFacade,
        private readonly TaskRemindProvider $taskRemindProvider
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        if (!$this->taskRemindProvider->isEnable()) {
            $io->error('Task reminder is not unable');

            return Command::FAILURE;
        }

        $offset = 0;
        $count = 0;
        while (true) {
            $tasks = $this->taskDeadlineFacade->getTasksAfterDeadline($offset, self::LIMIT);
            if (count($tasks) === 0) {
                break;
            }

            foreach ($tasks as $task) {
                $this->taskEventDispatcher->onTaskReminderCreate($task);
            }

            $count += count($tasks);
            $offset += self::LIMIT;
        }

        $io->success(sprintf('Task deadline reminds create for %d tasks', $count));

        return Command::SUCCESS;
    }
}
