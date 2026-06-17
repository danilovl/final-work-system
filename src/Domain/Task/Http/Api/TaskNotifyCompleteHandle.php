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

namespace App\Domain\Task\Http\Api;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\NotifyCompleteTask\NotifyCompleteTaskCommand;
use App\Domain\Task\Entity\Task;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class TaskNotifyCompleteHandle
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(Task $task): JsonResponse
    {
        if ($task->isNotifyComplete()) {
            return new JsonResponse(status: Response::HTTP_CONFLICT);
        }

        $command = NotifyCompleteTaskCommand::create($task);
        $this->commandBus->dispatch($command);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
