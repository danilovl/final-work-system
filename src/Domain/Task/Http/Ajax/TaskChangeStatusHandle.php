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

namespace App\Domain\Task\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Task\Bus\Command\ChangeStatusTask\ChangeStatusTaskCommand;
use App\Infrastructure\Service\RequestService;
use App\Domain\Task\Entity\Task;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TaskChangeStatusHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(string $type, Task $task): JsonResponse
    {
        $command = ChangeStatusTaskCommand::create($type, $task);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
