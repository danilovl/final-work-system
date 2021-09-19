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

namespace App\Command;

use App\EventDispatcher\TaskEventDispatcherService;
use App\Model\Task\Facade\TaskDeadlineFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskRemindDeadlineCommand extends Command
{
    protected static $defaultName = 'app:task-remind-deadline';
    private const LIMIT = 500;

    private SymfonyStyle $io;

    public function __construct(
        private TaskEventDispatcherService $taskEventDispatcherService,
        private TaskDeadlineFacade $taskDeadlineFacade,
        private ParameterServiceInterface $parameterService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create reminder notification emails for tasks');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->parameterService->get('task_remind.enable')) {
            $this->io->error('Task reminder is not unable');

            return Command::FAILURE;
        }

        $offset = 0;
        $count = 0;
        while (true) {
            $tasks = $this->taskDeadlineFacade->getTasksAfterDeadline($offset, static::LIMIT);
            if (count($tasks) === 0) {
                break;
            }

            foreach ($tasks as $task) {
                $this->taskEventDispatcherService->onTaskReminderCreate($task);
            }

            $count += count($tasks);
            $offset += static::LIMIT;
        }

        $this->io->success(sprintf('Task deadline reminds create for %d tasks', $count));

        return Command::SUCCESS;
    }
}
