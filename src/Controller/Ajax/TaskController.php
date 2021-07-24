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

use App\Model\Task\TaskModel;
use App\Constant\{
    TaskStatusConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Controller\BaseController;
use App\Entity\{
    Task,
    Work
};
use App\Form\TaskForm;
use App\Helper\FormValidationMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskController extends BaseController
{
    public function create(
        Request $request,
        Work $work
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->work = $work;
        $taskModel->owner = $this->getUser();

        $form = $this->createForm(TaskForm::class, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $this->get('app.factory.task')
                ->flushFromModel($taskModel);

            $this->get('app.event_dispatcher.task')
                ->onTaskCreate($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
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

        $taskModel = TaskModel::fromTask($task);
        $form = $this->createForm(TaskForm::class, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.task')
                ->flushFromModel($taskModel, $task);

            $this->get('app.event_dispatcher.task')
                ->onTaskEdit($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
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

        $type = $request->get('type');
        if (!empty($type)) {
            $taskEventDispatcherService = $this->get('app.event_dispatcher.task');

            switch ($type) {
                case TaskStatusConstant::ACTIVE:
                    $task->changeActive();

                    if ($task->isActive()) {
                        $taskEventDispatcherService->onTaskChangeStatus($task, $type);
                    }

                    break;
                case TaskStatusConstant::COMPLETE:
                    $task->changeComplete();

                    $taskEventDispatcherService->onTaskChangeStatus($task, $type);
                    break;
                case TaskStatusConstant::NOTIFY:
                    if ($task->isNotifyComplete()) {
                        $task->changeNotifyComplete();

                        $taskEventDispatcherService->onTaskChangeStatus($task, $type);
                    }
                    break;
            }

            $this->flushEntity($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function notifyComplete(
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::TASK_NOTIFY_COMPLETE, $task);

        if ($task->isNotifyComplete()) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
        }

        $task->changeNotifyComplete();
        $this->flushEntity($task);

        $this->get('app.event_dispatcher.task')
            ->onTaskNotifyComplete($task);

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="App\Entity\Task", options={"id" = "id_task"})
     */
    public function delete(
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $task);

        $this->removeEntity($task);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }

    public function completeAll(): JsonResponse
    {
        $user = $this->getUser();
        $taskFacade = $this->get('app.facade.task');

        if (!$taskFacade->isTasksCompleteByOwner($user, false)) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        $tasks = $taskFacade->findAllByOwnerComplete($user, false);
        foreach ($tasks as $task) {
            $task->changeComplete();
            $this->flushEntity($task);

            $this->get('app.event_dispatcher.task')
                ->onTaskChangeStatus($task, TaskStatusConstant::COMPLETE);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
