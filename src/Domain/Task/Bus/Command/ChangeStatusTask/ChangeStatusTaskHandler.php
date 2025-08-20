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

namespace App\Domain\Task\Bus\Command\ChangeStatusTask;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Application\Service\EntityManagerService;
use App\Domain\Task\Service\TaskStatusService;

readonly class ChangeStatusTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TaskStatusService $taskStatusService,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(ChangeStatusTaskCommand $command): void
    {
        $this->taskStatusService->changeStatus($command->type, $command->task);
        $this->entityManagerService->flush();
    }
}
