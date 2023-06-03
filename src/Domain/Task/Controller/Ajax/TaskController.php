<?php declare(strict_types=1);

/*
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskCreateHandle $taskCreateHandle,
        private readonly TaskCreateSeveralHandle $taskCreateSeveralHandle,
        private readonly TaskEditHandle $taskEditHandle,
        private readonly TaskChangeStatusHandle $taskChangeStatusHandle,
        private readonly TaskNotifyCompleteHandle $taskNotifyCompleteHandle,
        private readonly TaskDeleteHandle $taskDeleteHandle,
        private readonly TaskCompleteAllHandle $taskCompleteAllHandle
    ) {}

    public function create(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->taskCreateHandle->handle($request, $work);
    }

    public function createSeveral(Request $request): JsonResponse
    {
        return $this->taskCreateSeveralHandle->handle($request);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        return $this->taskEditHandle->handle($request, $task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function changeStatus(
        Request $request,
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        return $this->taskChangeStatusHandle->handle($request, $task);
    }

    public function notifyComplete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE, $task);

        return $this->taskNotifyCompleteHandle->handle($task);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_task'])]
    public function delete(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_task' => 'id'])] Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $task);

        return $this->taskDeleteHandle->handle($task);
    }

    public function completeAll(): JsonResponse
    {
        return $this->taskCompleteAllHandle->handle();
    }
}
