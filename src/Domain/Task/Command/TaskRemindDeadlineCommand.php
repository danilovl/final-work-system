<?php declare(strict_types=1);

/*
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
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskRemindDeadlineCommand extends Command
{
    final public const COMMAND_NAME = 'app:task-remind-deadline';

    private const int LIMIT = 500;

    private SymfonyStyle $io;

    public function __construct(
        private readonly TaskEventDispatcher $taskEventDispatcher,
        private readonly TaskDeadlineFacade $taskDeadlineFacade,
        private readonly TaskRemindProvider $taskRemindProvider
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Create reminder notification emails for tasks');
    }

    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->taskRemindProvider->isEnable()) {
            $this->io->error('Task reminder is not unable');

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

        $this->io->success(sprintf('Task deadline reminds create for %d tasks', $count));

        return Command::SUCCESS;
    }
}
