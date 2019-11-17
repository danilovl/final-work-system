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

use FinalWork\FinalWorkBundle\Model\User\UserModel;
use FinalWork\FinalWorkBundle\Model\WorkSearch\WorkSearchModel;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use Exception;
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use FinalWork\FinalWorkBundle\Model\Work\WorkModel;
use FinalWork\FinalWorkBundle\Constant\{
    SeoPageConstant,
    TabTypeConstant,
    FlashTypeConstant,
    WorkUserTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\FinalWorkBundle\Form\{
    UserForm,
    WorkForm,
    WorkSearchForm
};
use FinalWork\FinalWorkBundle\Helper\{
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
    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function createAction(Request $request): Response
    {
        $user = $this->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->getWorkForm(ControllerMethodConstant::CREATE, $workModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $work = $this->get('final_work.factory.work')
                    ->flushFromModel($workModel);

                $this->get('final_work.event_dispatcher.work')
                    ->onWorkCreate($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        $workDeadLineService = $this->get('final_work.facade.work.deadline');
        $workDeadLines = $workDeadLineService->getWorkDeadlinesBySupervisor(
            $user,
            $this->getParam('pagination.work.deadline_limit')
        );
        $workProgramDeadLines = $workDeadLineService->getWorkProgramDeadlinesBySupervisor(
            $user,
            $this->getParam('pagination.work.program_deadline_limit')
        );

        $this->get('final_work.seo_page')->setTitle('finalwork.page.work_create');

        return $this->render('@FinalWork/work/work.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.work_create'),
            'workDeadlines' => $workDeadLines->toArray(),
            'workProgramDeadlines' => $workProgramDeadLines->toArray(),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create')
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return Response
     */
    public function detailAction(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $work);

        $user = $this->getUser();
        $tabService = $this->get('final_work.work.detail_tab')
            ->setActiveTab($request->get('tab'));

        $paginationTask = $tabService->getTabPagination($request, TabTypeConstant::TAB_TASK, $work, $user);
        $paginationVersion = $tabService->getTabPagination($request, TabTypeConstant::TAB_VERSION, $work);
        $paginationEvent = $tabService->getTabPagination($request, TabTypeConstant::TAB_EVENT, $work);
        $paginationMessage = $tabService->getTabPagination($request, TabTypeConstant::TAB_MESSAGE, $work, $user);

        $this->get('final_work.seo_page')->setTitle($work->getTitle());

        return $this->render('@FinalWork/work/detail.html.twig', [
            'work' => $work,
            'tasks' => $paginationTask,
            'versions' => $paginationVersion,
            'messages' => $paginationMessage,
            'events' => $paginationEvent,
            'activeTab' => $tabService->getActiveTab(),
            'deleteForm' => $this->createDeleteForm($work, 'work_delete')->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param string $type
     * @return Response
     * @throws ORMException
     */
    public function listAction(
        Request $request,
        string $type
    ): Response {
        $openSearchTab = false;

        $workListService = $this->get('final_work.work.list');
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

        $this->get('final_work.seo_page')->setTitle('finalwork.text.work_list');

        return $this->render('@FinalWork/work/list.html.twig', [
            'form' => $form->createView(),
            'workGroups' => $pagination,
            'openSearchTab' => $openSearchTab,
            'deleteForms' => $deleteForms
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
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
                $this->get('final_work.factory.work')
                    ->flushFromModel($workModel, $work);

                $this->get('final_work.event_dispatcher.work')
                    ->onWorkEdit($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('work_edit', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        $workDeadLineService = $this->get('final_work.facade.work.deadline');
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

        $this->get('final_work.seo_page')->setTitle('finalwork.page.work_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/work/work.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.work_edit'),
            'workDeadlines' => $workDeadLines,
            'workProgramDeadlines' => $workProgramDeadLines,
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAuthorAction(
        Request $request,
        Work $work
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->createForm(UserForm::class, $userModel)
            ->remove('username')
            ->remove('role')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.user')
                    ->flushFromModel($userModel, $author);

                $this->get('final_work.event_dispatcher.work')
                    ->onWorkEditAuthor($work);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('work_edit_author', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        $this->get('final_work.seo_page')
            ->setTitle('finalwork.page.profile_edit')
            ->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/work/edit_author.html.twig'), [
            'work' => $work,
            'user' => $author,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @return RedirectResponse
     */
    public function deleteAction(
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

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.delete.success');

                return $this->redirectToRoute('work_list', [
                    'type' => WorkUserTypeConstant::SUPERVISOR
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');

            return $this->redirectToRoute('work_detail', [
                'id' => $this->hashIdEncode($work->getId())
            ]);
        }

        return $this->redirectToRoute('work_list', [
            'type' => WorkUserTypeConstant::SUPERVISOR
        ]);
    }

    /**
     * @param string $type
     * @param WorkModel $workModel
     * @param Work|null $work
     * @return FormInterface
     */
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
                    'method' => Request::METHOD_POST,
                ]);
                break;
            default:
                throw new ConstantNotFoundException('Controller method type constant not found');
        }

        return $this->createForm(WorkForm::class, $workModel, $parameters);
    }

    /**
     * @param string $type
     * @param WorkSearchModel $workSearchModel
     * @return FormInterface
     */
    public function getSearchForm(string $type, WorkSearchModel $workSearchModel): FormInterface
    {
        $user = $this->getUser();

        $workListService = $this->get('final_work.work.list');

        $userAuthorArray = $workListService->getUserAuthors($user, $type)->toArray();
        $userOpponentArray = $workListService->getUserOpponents($user, $type)->toArray();
        $userConsultantArray = $workListService->getUserConsultants($user, $type)->toArray();
        $userSupervisorArray = $workListService->getUserSupervisors($user, $type)->toArray();

        SortFunctionHelper::usortCzechArray($userAuthorArray);
        SortFunctionHelper::usortCzechArray($userOpponentArray);
        SortFunctionHelper::usortCzechArray($userConsultantArray);
        SortFunctionHelper::usortCzechArray($userSupervisorArray);

        $workDeadLines = $this->get('final_work.facade.work.deadline')
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
