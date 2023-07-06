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

use App\Application\Constant\{
    TaskStatusConstant
};
use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService
};
use App\Application\Service\EntityManagerService;
use App\Application\Service\UserService;
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;
use App\Domain\Task\Facade\TaskFacade;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TaskCompleteAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TaskFacade $taskFacade,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {}

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();

        if (!$this->taskFacade->isTasksCompleteByOwner($user, false)) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        $tasks = $this->taskFacade->findAllByOwnerComplete($user, false);
        foreach ($tasks as $task) {
            $task->changeComplete();
            $this->entityManagerService->flush();

            $this->taskEventDispatcherService->onTaskChangeStatus(
                $task,
                TaskStatusConstant::COMPLETE->value
            );
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
