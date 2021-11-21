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

use App\Model\Task\Entity\Task;
use App\Constant\AjaxJsonTypeConstant;
use App\Model\Task\EventDispatcher\TaskEventDispatcherService;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskNotifyCompleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {
    }

    public function handle(Task $task): JsonResponse
    {
        if ($task->isNotifyComplete()) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
        }

        $task->changeNotifyComplete();
        $this->entityManagerService->flush($task);

        $this->taskEventDispatcherService->onTaskNotifyComplete($task);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
