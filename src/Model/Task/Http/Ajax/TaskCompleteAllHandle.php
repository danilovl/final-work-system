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

namespace App\Model\Task\Http\Ajax;

use App\Model\Task\EventDispatcher\TaskEventDispatcherService;
use App\Model\Task\Facade\TaskFacade;
use App\Constant\{
    TaskStatusConstant,
    AjaxJsonTypeConstant
};
use App\Service\{
    UserService,
    EntityManagerService,
    RequestService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskCompleteAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private TaskFacade $taskFacade,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {
    }

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();

        if (!$this->taskFacade->isTasksCompleteByOwner($user, false)) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        $tasks = $this->taskFacade->findAllByOwnerComplete($user, false);
        foreach ($tasks as $task) {
            $task->changeComplete();
            $this->entityManagerService->flush($task);

            $this->taskEventDispatcherService
                ->onTaskChangeStatus($task, TaskStatusConstant::COMPLETE);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
