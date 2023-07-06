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
use App\Application\Service\{
    RequestService
};
use App\Application\Service\EntityManagerService;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\TaskEventDispatcherService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TaskNotifyCompleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService,
        private TaskEventDispatcherService $taskEventDispatcherService
    ) {}

    public function handle(Task $task): JsonResponse
    {
        if ($task->isNotifyComplete()) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
        }

        $task->changeNotifyComplete();
        $this->entityManagerService->flush();

        $this->taskEventDispatcherService->onTaskNotifyComplete($task);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
