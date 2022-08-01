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
    RequestService,
    EntityManagerService
};
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Service\TaskStatusService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class TaskChangeStatusHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly TaskStatusService $taskStatusService,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function handle(Request $request, Task $task): JsonResponse
    {
        $type = $request->attributes->get('type');
        if (!empty($type)) {
            $this->taskStatusService
                ->changeStatus($type, $task);

            $this->entityManagerService->flush($task);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
    }
}
