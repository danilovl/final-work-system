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

use App\Model\User\UserModel;
use App\Model\WorkSearch\WorkSearchModel;
use App\Exception\ConstantNotFoundException;
use App\Model\Work\WorkModel;
use App\Constant\{
    SeoPageConstant,
    TabTypeConstant,
    FlashTypeConstant,
    WorkUserTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\Work;
use App\Form\{
    UserEditForm,
    WorkForm,
    WorkSearchForm
};
use App\Helper\{
    SortFunctionHelper,
    WorkFunctionHelper
};
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkController extends BaseController
{
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->getWorkForm(ControllerMethodConstant::CREATE, $workModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $work = $this->get('app.factory.work')
                    ->flushFromModel($workModel);

                $this->get('app.event_dispatcher.work')
                    ->onWorkCreate($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        $workDeadLineService = $this->get('app.facade.work.deadline');
        $workDeadLines = $workDeadLineService->getWorkDeadlinesBySupervisor(
            $user,
            $this->getParam('pagination.work.deadline_limit')
        );
        $workProgramDeadLines = $workDeadLineService->getWorkProgramDeadlinesBySupervisor(
            $user,
            $this->getParam('pagination.work.program_deadline_limit')
        );

        return $this->render('work/work.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.work_create'),
            'workDeadlines' => $workDeadLines->toArray(),
            'workProgramDeadlines' => $workProgramDeadLines->toArray(),
            'buttonActionTitle' => $this->trans('app.form.action.create')
        ]);
    }

    public function detail(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $work);

        $user = $this->getUser();
        $tabService = $this->get('app.work.detail_tab')
            ->setActiveTab($request->get('tab'));

        $paginationTask = $tabService->getTabPagination($request, TabTypeConstant::TAB_TASK, $work, $user);
        $paginationVersion = $tabService->getTabPagination($request, TabTypeConstant::TAB_VERSION, $work);
        $paginationEvent = $tabService->getTabPagination($request, TabTypeConstant::TAB_EVENT, $work);
        $paginationMessage = $tabService->getTabPagination($request, TabTypeConstant::TAB_MESSAGE, $work, $user);

        $this->get('app.facade.conversation_message')->setIsReadToConversationMessages($paginationMessage, $user);

        $this->get('app.seo_page')->setTitle($work->getTitle());

        return $this->render('work/detail.html.twig', [
            'work' => $work,
            'tasks' => $paginationTask,
            'versions' => $paginationVersion,
            'messages' => $paginationMessage,
            'events' => $paginationEvent,
            'activeTab' => $tabService->getActiveTab(),
            'deleteForm' => $this->createDeleteForm($work, 'work_delete')->createView()
        ]);
    }

    public function list(
        Request $request,
        string $type
    ): Response {
        $openSearchTab = false;

        $workListService = $this->get('app.work.list');
        $works = $workListService->getWorkList($this->getUser(), $type);

        $form = $this->getSearchForm($type, new WorkSearchModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            $openSearchTab = true;
        }

        $works = $workListService->filter($form, $works);
        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR:
                $workGroups = WorkFunctionHelper::groupWorksByCategoryAndSorting($works);
                break;
            default:
                $workGroups = WorkFunctionHelper::groupWorksByDeadline($works);
                break;
        }

        $pagination = $this->createPagination($request, $workGroups);

        $deleteForms = [];
        foreach ($pagination as $entities) {
            $entities = $entities['works'] ?? $entities;

            /** @var Work $entity */
            foreach ($entities as $entity) {
                $deleteForms[$entity->getId()] = $this->createDeleteForm($entity, 'work_delete')->createView();
            }
        }

        return $this->render('work/list.html.twig', [
            'form' => $form->createView(),
            'workGroups' => $pagination,
            'openSearchTab' => $openSearchTab,
            'deleteForms' => $deleteForms
        ]);
    }

    public function edit(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $user = $this->getUser();

        $workModel = WorkModel::fromWork($work);
        $form = $this->getWorkForm(ControllerMethodConstant::EDIT, $workModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.work')
                    ->flushFromModel($workModel, $work);

                $this->get('app.event_dispatcher.work')
                    ->onWorkEdit($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('work_edit', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $workDeadLineService = $this->get('app.facade.work.deadline');
        $workDeadLines = $workDeadLineService
            ->getWorkDeadlinesBySupervisor(
                $user,
                $this->getParam('pagination.work.deadline_limit')
            )->toArray();

        $workProgramDeadLines = $workDeadLineService
            ->getWorkProgramDeadlinesBySupervisor(
                $user,
                $this->getParam('pagination.work.program_deadline_limit')
            )->toArray();

        if ($request->isXmlHttpRequest()) {
            $form = $this->getWorkForm(ControllerMethodConstant::EDIT_AJAX, $workModel, $work);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'work/work.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.work_edit'),
            'workDeadlines' => $workDeadLines,
            'workProgramDeadlines' => $workProgramDeadLines,
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function editAuthor(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->createForm(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.user')
                    ->flushFromModel($userModel, $author);

                $this->get('app.event_dispatcher.work')
                    ->onWorkEditAuthor($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('work_edit_author', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        $this->get('app.seo_page')->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, 'work/edit_author.html.twig'), [
            'work' => $work,
            'user' => $author,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function delete(
        Request $request,
        Work $work
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        $form = $this->createDeleteForm($work, 'work_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $workMedia = $work->getMedias();
                if ($workMedia !== null) {
                    foreach ($workMedia as $media) {
                        $deleteFile = $media->getWebPath();
                        (new Filesystem)->remove($deleteFile);
                    }
                }

                $this->removeEntity($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');

                return $this->redirectToRoute('work_list', [
                    'type' => WorkUserTypeConstant::SUPERVISOR
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');

            return $this->redirectToRoute('work_detail', [
                'id' => $this->hashIdEncode($work->getId())
            ]);
        }

        return $this->redirectToRoute('work_list', [
            'type' => WorkUserTypeConstant::SUPERVISOR
        ]);
    }

    public function getWorkForm(
        string $type,
        WorkModel $workModel,
        Work $work = null
    ): FormInterface {
        $parameters = [
            'user' => $this->getUser()
        ];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('task_create_ajax', [
                        'id' => $this->hashIdEncode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = array_merge($parameters, [
                    'action' => $this->generateUrl('work_edit_ajax', [
                        'id' => $this->hashIdEncode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ]);
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->createForm(WorkForm::class, $workModel, $parameters);
    }

    public function getSearchForm(string $type, WorkSearchModel $workSearchModel): FormInterface
    {
        $user = $this->getUser();

        $workListService = $this->get('app.work.list');

        $userAuthorArray = $workListService->getUserAuthors($user, $type)->toArray();
        $userOpponentArray = $workListService->getUserOpponents($user, $type)->toArray();
        $userConsultantArray = $workListService->getUserConsultants($user, $type)->toArray();
        $userSupervisorArray = $workListService->getUserSupervisors($user, $type)->toArray();

        SortFunctionHelper::usortCzechArray($userAuthorArray);
        SortFunctionHelper::usortCzechArray($userOpponentArray);
        SortFunctionHelper::usortCzechArray($userConsultantArray);
        SortFunctionHelper::usortCzechArray($userSupervisorArray);

        $workDeadLines = $this->get('app.facade.work.deadline')
            ->getWorkDeadlinesBySupervisor($user)
            ->toArray();

        return $this->createForm(WorkSearchForm::class, $workSearchModel, [
            'authors' => $userAuthorArray,
            'opponents' => $userOpponentArray,
            'consultants' => $userConsultantArray,
            'supervisors' => $userSupervisorArray,
            'deadlines' => $workDeadLines
        ]);
    }
}
