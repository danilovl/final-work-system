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

namespace App\Controller;

use App\Model\Task\TaskModel;
use App\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\{
    Task,
    Work
};
use App\Form\TaskForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class TaskController extends BaseController
{
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $tasksQuery = $this->get('app.facade.task')
            ->queryTasksByOwner($user);

        $isTasksInComplete = $this->get('app.facade.task')
            ->isTasksCompleteByOwner($user, false);

        return $this->render('task/list.html.twig', [
            'isTasksInComplete' => $isTasksInComplete,
            'tasks' => $this->createPagination($request, $tasksQuery)
        ]);
    }

    public function create(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $user = $this->getUser();
        $taskName = $request->get('taskName');

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->work = $work;
        $taskModel->owner = $user;

        $createFromEvent = false;
        if (!empty($taskName)) {
            $taskModel->name = $taskName;
            $createFromEvent = true;
        }

        $form = $this->getTaskForm(ControllerMethodConstant::CREATE, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $task = $this->get('app.factory.task')
                    ->flushFromModel($taskModel);

                $this->get('app.event_dispatcher.task')
                    ->onTaskCreate($task);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getTaskForm(ControllerMethodConstant::CREATE_AJAX, $taskModel, null, $work);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'task/task.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.task_create'),
            'createFromEvent' => $createFromEvent,
            'taskDeadlines' => $this->get('app.facade.task_deadline')->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
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
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        $taskModel = TaskModel::fromTask($task);
        $form = $this->getTaskForm(ControllerMethodConstant::EDIT, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.task')
                    ->flushFromModel($taskModel, $task);

                $this->get('app.event_dispatcher.task')
                    ->onTaskEdit($task);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $form = $this->getTaskForm(ControllerMethodConstant::EDIT_AJAX, $taskModel, $task, $work);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'task/task.html.twig'), [
            'work' => $work,
            'task' => $task,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.task_edit'),
            'taskDeadlines' => $this->get('app.facade.task_deadline')->getDeadlinesByOwner($this->getUser()),
            'createFromEvent' => false,
            'buttonActionTitle' => $this->trans('app.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function getTaskForm(
        string $type,
        TaskModel $taskModel,
        ?Task $task = null,
        ?Work $work = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for create ajax');
                }

                $parameters = [
                    'action' => $this->generateUrl('task_create_ajax', [
                        'id' => $this->hashIdEncode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = [
                    'action' => $this->generateUrl('task_edit_ajax', [
                        'id_task' => $this->hashIdEncode($task->getId()),
                        'id_work' => $this->hashIdEncode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->createForm(TaskForm::class, $taskModel, $parameters);
    }
}
