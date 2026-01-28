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

namespace App\Domain\Work\Command;

use App\Domain\Work\EventDispatcher\WorkEventDispatcher;
use App\Domain\Work\Facade\WorkDeadlineFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:work-remind-deadline', description: 'Create reminder notification emails for works')]
class WorkRemindDeadlineCommand
{
    private const int LIMIT = 500;

    public function __construct(
        private readonly WorkEventDispatcher $workEventDispatcher,
        private readonly WorkDeadlineFacade $workDeadlineFacade,
        private readonly ParameterServiceInterface $parameterService
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        if (!$this->parameterService->getBoolean('work_remind.enable')) {
            $io->error('Work reminder is not unable');

            return Command::FAILURE;
        }

        $offset = 0;
        $count = 0;

        while (true) {
            $works = $this->workDeadlineFacade->getWorksAfterDeadline($offset, self::LIMIT);
            if (count($works) === 0) {
                break;
            }

            foreach ($works as $work) {
                $this->workEventDispatcher->onWorkReminderDeadlineCreate($work);
            }

            $count += count($works);
            $offset += self::LIMIT;
        }

        $io->success(sprintf('Work deadline reminds create for %d works', $count));

        return Command::SUCCESS;
    }
}
