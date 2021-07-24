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

use App\DataTransferObject\Repository\{
    WorkData,
    WorkStatusData
};
use Doctrine\Common\Collections\ArrayCollection;
use App\Model\User\UserModel;
use App\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    WorkUserTypeConstant,
    ControllerMethodConstant
};
use App\Form\WorkSearchStatusForm;
use App\Helper\UserHelper;
use App\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserController extends BaseController
{
    public function create(Request $request): Response
    {
        $userFormFactory = $this->get('app.form_factory.user');
        $userFacade = $this->get('app.facade.user');
        $userModel = new UserModel;

        $form = $userFormFactory->getUserForm(ControllerMethodConstant::CREATE, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email = $userModel->email;
                $username = $userModel->username;

                if ($userFacade->findOneByUsername($username) || $userFacade->findOneByEmail($email)) {
                    $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.user.create.error');
                    $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.user.create.warning');
                } else {
                    $newUser = $this->get('app.factory.user')->createNewUser($userModel);

                    $this->get('app.event_dispatcher.user')
                        ->onUserCreate($newUser);

                    $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.user.create.success');
                }
            } else {
                $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
                $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            }
        }

        if ($request->isXmlHttpRequest()) {
            $form = $userFormFactory->getUserForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userModel
            );
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'user/user.html.twig'), [
            'reload' => true,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.user_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function edit(
        Request $request,
        User $user
    ): Response {
        $userFormFactory = $this->get('app.form_factory.user');
        $userModel = UserModel::fromUser($user);

        $form = $userFormFactory->getUserForm(ControllerMethodConstant::EDIT, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.user')
                    ->flushFromModel($userModel, $user);

                $this->get('app.event_dispatcher.user')
                    ->onUserEdit($user, $this->getUser());

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('user_edit', [
                    'id' => $this->hashIdEncode($user->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $userFormFactory->getUserForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userModel,
                $user
            );
        }

        $this->get('app.seo_page')->addTitle($user->getUsername(), SeoPageConstant::VERTICAL_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, 'user/user.html.twig'), [
            'reload' => true,
            'user' => $user,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.user_edit'),
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function userList(Request $request): Response
    {
        $user = $this->getUser();
        $userService = $this->get('app.facade.user');

        $type = $request->get('type');
        $openSearchTab = false;
        $showSearchTab = true;
        $workStatus = null;

        $usersQuery = $userService->getUsersQueryBySupervisor($user, $type);

        $form = $this->createForm(WorkSearchStatusForm::class)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            $openSearchTab = true;

            if ($form->isValid()) {
                $workStatus = $form->get('status')->getData();
                $usersQuery = $userService->getUsersQueryBySupervisor($user, $type, $workStatus);
            }
        }

        $getUserWorkAndStatus = true;
        switch ($type) {
            case WorkUserTypeConstant::AUTHOR:
                $title = $this->trans('app.text.author_list');

                break;
            case WorkUserTypeConstant::OPPONENT:
                $title = $this->trans('app.text.opponent_list');

                break;
            case WorkUserTypeConstant::CONSULTANT:
                $title = $this->trans('app.text.consultant_list');

                break;
            default:
                $showSearchTab = false;
                $getUserWorkAndStatus = false;
                $usersQuery = $userService->queryUnusedUsers($user);
                $title = $this->trans('app.text.unused_user_list');

                break;
        }

        $pagination = $this->createPagination($request, $usersQuery);
        $works = new ArrayCollection;
        $userStatusWorkCounts = new ArrayCollection;

        if ($getUserWorkAndStatus === true) {
            foreach ($pagination as $paginationUser) {

                $workData = WorkData::createFromArray([
                    'user' => $paginationUser,
                    'supervisor' => $user,
                    'type' => $type,
                    'workStatus' => $workStatus
                ]);

                $paginationUserWorks = $this->get('app.facade.work')->getWorksByAuthorSupervisorStatus($workData);

                if ($works->get($paginationUser->getId()) === null) {
                    $works->set($paginationUser->getId(), $paginationUserWorks);
                }

                $workStatusData = WorkStatusData::createFromArray([
                    'user' => $paginationUser,
                    'supervisor' => $user,
                    'type' => $type,
                    'workStatus' => $workStatus
                ]);

                $workStatusCount = $this->get('app.facade.work_status')->getCountByUser($workStatusData);

                if ($userStatusWorkCounts->get($paginationUser->getId()) === null) {
                    $userStatusWorkCounts->set($paginationUser->getId(), $workStatusCount);
                }
            }
        }

        $this->get('app.seo_page')->setTitle($title);

        return $this->render('user/user_list.html.twig', [
            'type' => $type,
            'title' => $title,
            'users' => $pagination,
            'userWorks' => $works,
            'userStatusWorkCounts' => $userStatusWorkCounts,
            'form' => $form->createView(),
            'openSearchTab' => $openSearchTab,
            'showSearchTab' => $showSearchTab,
            'userHelper' => new UserHelper
        ]);
    }
}
