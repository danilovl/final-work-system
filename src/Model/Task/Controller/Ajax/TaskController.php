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

namespace App\Model\Task\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\Task\Http\Ajax\{
    TaskEditHandle,
    TaskCreateHandle,
    TaskDeleteHandle,
    TaskCompleteAllHandle,
    TaskChangeStatusHandle,
    TaskCreateSeveralHandle,
    TaskNotifyCompleteHandle
};
use App\Entity\{
    Task,
    Work
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskController extends BaseController
{
    public function __construct(
        private TaskCreateHandle $taskCreateHandle,
        private TaskCreateSeveralHandle $taskCreateSeveralHandle,
        private TaskEditHandle $taskEditHandle,
        private TaskChangeStatusHandle $taskChangeStatusHandle,
        private TaskNotifyCompleteHandle $taskNotifyCompleteHandle,
        private TaskDeleteHandle $taskDeleteHandle,
        private TaskCompleteAllHandle $taskCompleteAllHandle
    ) {
    }

    public function create(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->taskCreateHandle->handle($request, $work);
    }

    public function createSeveral(Request $request): JsonResponse
    {
        return $this->taskCreateSeveralHandle->handle($request);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function edit(
        Request $request,
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        return $this->taskEditHandle->handle($request, $task);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function changeStatus(
        Request $request,
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        return $this->taskChangeStatusHandle->handle($request, $task);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function notifyComplete(Work $work, Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE, $task);

        return $this->taskNotifyCompleteHandle->handle($task);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function delete(Work $work, Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $task);

        return $this->taskDeleteHandle->handle($task);
    }

    public function completeAll(): JsonResponse
    {
        return $this->taskCompleteAllHandle->handle();
    }
}
