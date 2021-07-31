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

use App\DataTransferObject\Form\Factory\TaskFormFactoryData;
use App\Model\Task\TaskModel;
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\{
    Task,
    Work
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

        $taskFormFactory = $this->get('app.form_factory.task');
        $user = $this->getUser();
        $taskName = $request->get('taskName');

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->work = $work;
        $taskModel->owner = $user;

        if (!empty($taskName)) {
            $taskModel->name = $taskName;
        }

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::CREATE,
            'taskModel' => $taskModel
        ]);

        $form = $taskFormFactory->getTaskForm($taskFormFactoryData)
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
            $taskFormFactoryData = TaskFormFactoryData::createFromArray([
                'type' => ControllerMethodConstant::CREATE_AJAX,
                'taskModel' => $taskModel,
                'work' => $taskModel
            ]);

            $form = $taskFormFactory->getTaskForm($taskFormFactoryData);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'task/task.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.task_create'),
            'taskDeadlines' => $this->get('app.facade.task_deadline')->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function createSeveral(Request $request): Response
    {
        $taskFormFactory = $this->get('app.form_factory.task');
        $user = $this->getUser();

        $taskModel = new TaskModel;
        $taskModel->active = true;
        $taskModel->owner = $user;

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::CREATE_SEVERAL,
            'taskModel' => $taskModel,
            'options' => [
                'supervisor' => $user
            ]
        ]);

        $form = $taskFormFactory->getTaskForm($taskFormFactoryData)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                foreach ($taskModel->works as $work) {
                    $taskModel->work = $work;

                    $task = $this->get('app.factory.task')
                        ->flushFromModel($taskModel);

                    $this->get('app.event_dispatcher.task')
                        ->onTaskCreate($task);
                }

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('task_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $taskFormFactoryData->type = ControllerMethodConstant::CREATE_SEVERAL_AJAX;

            $form = $taskFormFactory->getTaskForm($taskFormFactoryData);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'task/task.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.task_create'),
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

        $taskFormFactory = $this->get('app.form_factory.task');
        $taskModel = TaskModel::fromTask($task);

        $taskFormFactoryData = TaskFormFactoryData::createFromArray([
            'type' => ControllerMethodConstant::EDIT,
            'taskModel' => $taskModel
        ]);

        $form = $taskFormFactory->getTaskForm($taskFormFactoryData)->handleRequest($request);

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
            $taskFormFactoryData = TaskFormFactoryData::createFromArray([
                'type' => ControllerMethodConstant::EDIT_AJAX,
                'taskModel' => $taskModel,
                'task' => $task,
                'work' => $work
            ]);

            $form = $taskFormFactory->getTaskForm($taskFormFactoryData);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'task/task.html.twig'), [
            'work' => $work,
            'task' => $task,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.task_edit'),
            'taskDeadlines' => $this->get('app.facade.task_deadline')->getDeadlinesByOwner($this->getUser()),
            'buttonActionTitle' => $this->trans('app.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }
}
