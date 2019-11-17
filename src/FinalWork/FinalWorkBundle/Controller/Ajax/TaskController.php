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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Model\Task\TaskModel;
use FinalWork\FinalWorkBundle\Constant\{
    TaskStatusConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\{
    Task,
    Work
};
use FinalWork\FinalWorkBundle\Form\TaskForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class TaskController extends BaseController
{
    /**
     * @param Request $request
     * @param Work $work
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(
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
            $task = $this->get('final_work.factory.task')
                ->flushFromModel($taskModel);

            $this->get('final_work.event_dispatcher.task')
                ->onTaskCreate($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @param Task $task
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="FinalWork\FinalWorkBundle\Entity\Task", options={"id" = "id_task"})
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        $taskModel = TaskModel::fromTask($task);
        $form = $this->createForm(TaskForm::class, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.task')
                ->flushFromModel($taskModel, $task);

            $this->get('final_work.event_dispatcher.task')
                ->onTaskEdit($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @param Task $task
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="FinalWork\FinalWorkBundle\Entity\Task", options={"id" = "id_task"})
     *
     * @return JsonResponse
     */
    public function changeStatusAction(
        Request $request,
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        $type = $request->get('type');
        if (!empty($type)) {
            $taskEventDispatcherService = $this->get('final_work.event_dispatcher.task');

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

            $this->flushEntity();

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
    }

    /**
     * @param Work $work
     * @param Task $task
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="FinalWork\FinalWorkBundle\Entity\Task", options={"id" = "id_task"})
     *
     * @return JsonResponse
     */
    public function notifyCompleteAction(
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        if (!$task->isNotifyComplete()) {
            $task->changeNotifyComplete();
            $this->flushEntity();

            $this->get('final_work.event_dispatcher.task')
                ->onTaskCreate($task);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
    }

    /**
     * @param Work $work
     * @param Task $task
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="FinalWork\FinalWorkBundle\Entity\Task", options={"id" = "id_task"})
     *
     * @return JsonResponse
     */
    public function deleteAction(
        Work $work,
        Task $task
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $task);

        $this->removeEntity($task);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
