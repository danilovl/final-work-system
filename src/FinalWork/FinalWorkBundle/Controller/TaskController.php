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

namespace FinalWork\FinalWorkBundle\Controller;

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Model\Task\TaskModel;
use FinalWork\FinalWorkBundle\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Task,
    Work
};
use FinalWork\FinalWorkBundle\Form\TaskForm;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class TaskController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $tasksQuery = $this->get('final_work.facade.task')
            ->queryTasksByOwner($this->getUser());

        $this->get('final_work.seo_page')->setTitle('finalwork.page.task_list');

        return $this->render('@FinalWork/task/list.html.twig', [
            'tasks' => $this->createPagination($request, $tasksQuery)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(
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
                $task = $this->get('final_work.factory.task')
                    ->flushFromModel($taskModel);

                $this->get('final_work.event_dispatcher.task')
                    ->onTaskCreate($task);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getTaskForm(ControllerMethodConstant::CREATE_AJAX, $taskModel, null, $work);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.task_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/task/task.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.task_create'),
            'createFromEvent' => $createFromEvent,
            'taskDeadlines' => $this->get('final_work.facade.task_deadline')->getDeadlinesByOwner($user),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @param Task $task
     * @return Response
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("task", class="FinalWork\FinalWorkBundle\Entity\Task", options={"id" = "id_task"})
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Work $work,
        Task $task
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $task);

        $taskModel = TaskModel::fromTask($task);
        $form = $form = $this->getTaskForm(ControllerMethodConstant::EDIT, $taskModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.task')
                    ->flushFromModel($taskModel, $task);

                $this->get('final_work.event_dispatcher.task')
                    ->onTaskEdit($task);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $form = $this->getTaskForm(ControllerMethodConstant::EDIT_AJAX, $taskModel, $task, $work);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.task_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/task/task.html.twig'), [
            'work' => $work,
            'task' => $task,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.task_edit'),
            'taskDeadlines' => $this->get('final_work.facade.task_deadline')->getDeadlinesByOwner($this->getUser()),
            'createFromEvent' => false,
            'buttonActionTitle' => $this->trans('finalwork.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param string $type
     * @param TaskModel $taskModel
     * @param Task|null $task
     * @param Work|null $work
     * @return FormInterface
     */
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
