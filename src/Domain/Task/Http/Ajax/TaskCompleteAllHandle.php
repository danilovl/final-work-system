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
use App\Domain\Task\Bus\Command\CompleteTask\CompleteTaskCommand;
use App\Application\Service\RequestService;
use App\Domain\Task\Facade\TaskFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TaskCompleteAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TaskFacade $taskFacade,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();

        if (!$this->taskFacade->isTasksCompleteByOwner($user, false)) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        $tasks = $this->taskFacade->findAllByOwnerComplete($user, false);
        foreach ($tasks as $task) {
            $createTaskCommand = new CompleteTaskCommand($task);
            $this->commandBus->dispatch($createTaskCommand);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
