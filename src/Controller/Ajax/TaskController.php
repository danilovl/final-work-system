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

namespace App\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
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
    public function create(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->get('app.http_handle_ajax.task.create')->handle($request, $work);
    }

    public function createSeveral(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.task.create_several')->handle($request);
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

        return $this->get('app.http_handle_ajax.task.edit')->handle($request, $task);
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

        return $this->get('app.http_handle_ajax.task.change_status')->handle($request, $task);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function notifyComplete(Work $work, Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE, $task);

        return $this->get('app.http_handle_ajax.task.notify_complete')->handle($task);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function delete(Work $work, Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $task);

        return $this->get('app.http_handle_ajax.task.delete')->handle($task);
    }

    public function completeAll(): JsonResponse
    {
        return $this->get('app.http_handle_ajax.task.complete_all')->handle();
    }
}
