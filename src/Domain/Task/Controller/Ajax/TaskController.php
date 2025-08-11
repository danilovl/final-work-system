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

namespace App\Domain\Task\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\Task\Entity\Task;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Domain\Task\Http\Ajax\{
    TaskEditHandle,
    TaskCreateHandle,
    TaskDeleteHandle,
    TaskChangeStatusHandle,
    TaskCompleteAllHandle,
    TaskCreateSeveralHandle,
    TaskNotifyCompleteHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class TaskController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private TaskCreateHandle $taskCreateHandle,
        private TaskCreateSeveralHandle $taskCreateSeveralHandle,
        private TaskEditHandle $taskEditHandle,
        private TaskChangeStatusHandle $taskChangeStatusHandle,
        private TaskNotifyCompleteHandle $taskNotifyCompleteHandle,
        private TaskDeleteHandle $taskDeleteHandle,
        private TaskCompleteAllHandle $taskCompleteAllHandle
    ) {}

    public function create(Request $request, Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->taskCreateHandle->__invoke($request, $work);
    }

    public function createSeveral(Request $request): JsonResponse
    {
        return $this->taskCreateSeveralHandle->__invoke($request);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $task);

        return $this->taskEditHandle->__invoke($request, $task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function changeStatus(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $task);

        return $this->taskChangeStatusHandle->__invoke($request, $task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function notifyComplete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE->value, $task);

        return $this->taskNotifyCompleteHandle->__invoke($task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function delete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $task);

        return $this->taskDeleteHandle->__invoke($task);
    }

    public function completeAll(): JsonResponse
    {
        return $this->taskCompleteAllHandle->__invoke();
    }
}
