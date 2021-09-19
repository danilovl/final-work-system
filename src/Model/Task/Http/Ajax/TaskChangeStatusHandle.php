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

use App\Entity\Task;
use App\Model\Task\Service\TaskStatusService;
use App\Constant\AjaxJsonTypeConstant;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskChangeStatusHandle
{
    public function __construct(
        private RequestService $requestService,
        private TaskStatusService $taskStatusService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(Request $request, Task $task): JsonResponse
    {
        $type = $request->get('type');
        if (!empty($type)) {
            $this->taskStatusService
                ->changeStatus($type, $task);

            $this->entityManagerService->flush($task);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
    }
}
