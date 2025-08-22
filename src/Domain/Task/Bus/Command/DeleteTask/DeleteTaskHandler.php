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

namespace App\Domain\Task\Bus\Command\DeleteTask;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Application\Service\EntityManagerService;

readonly class DeleteTaskHandler implements CommandHandlerInterface
{
    public function __construct(private EntityManagerService $entityManagerService) {}

    public function __invoke(DeleteTaskCommand $command): void
    {
        $this->entityManagerService->remove($command->task);
    }
}
