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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use Exception;
use FinalWork\FinalWorkBundle\Model\User\UserModel;
use FinalWork\FinalWorkBundle\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    WorkUserTypeConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Form\{
    UserForm,
    WorkSearchStatusForm
};
use FinalWork\FinalWorkBundle\Helper\{
    UserHelper,
    FunctionHelper
};
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class UserController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws OutOfBoundsException
     * @throws Exception
     */
    public function createAction(Request $request): Response
    {
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $userModel = new UserModel;

        $form = $this->getUserForm(ControllerMethodConstant::CREATE, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email = $userModel->email;
                $username = $userModel->username;

                if ($userManager->findUserByUsername($username) || $userManager->findUserByEmail($email)) {
                    $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.user.create.error');
                    $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.user.create.warning');
                } else {
                    $password = FunctionHelper::randomPassword(8);

                    $newUser = $userManager->createUser();
                    $newUser->setUsername($username);
                    $newUser->setEmail($email);
                    $newUser->setPlainPassword($password);
                    $newUser->setEnabled(true);
                    $newUser->addRole($userModel->role);
                    $newUser->setDegreeBefore($userModel->degreeBefore);
                    $newUser->setFirstname($userModel->firstName);
                    $newUser->setLastname($userModel->lastName);
                    $newUser->setDegreeAfter($userModel->degreeAfter);

                    $this->createEntity($newUser);

                    $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.user.create.success');
                    $newUser->setPassword($password);

                    $this->get('final_work.event_dispatcher.user')
                        ->onUserCreate($newUser);
                }
            } else {
                $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
                $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            }
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getUserForm(ControllerMethodConstant::CREATE_AJAX, $userModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.user_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/user/user.html.twig'), [
            'reload' => true,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.user_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        User $user
    ): Response {
        $userModel = UserModel::fromUser($user);
        $form = $this->getUserForm(ControllerMethodConstant::EDIT, $userModel)
            ->remove('username')
            ->remove('role')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.user')
                    ->flushFromModel($userModel, $user);

                $this->get('final_work.event_dispatcher.user')
                    ->onUserEdit($user);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('user_edit', [
                    'id' => $this->hashIdEncode($user->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getUserForm(ControllerMethodConstant::EDIT_AJAX, $userModel, $user)
                ->remove('username')
                ->remove('role');
        }

        $this->get('final_work.seo_page')
            ->setTitle('finalwork.page.user_edit')
            ->addTitle($user->getUsername(), SeoPageConstant::VERTICAL_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/user/user.html.twig'), [
            'reload' => true,
            'user' => $user,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.user_edit'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws LogicException
     */
    public function userListAction(Request $request): Response
    {
        $user = $this->getUser();
        $userService = $this->get('final_work.facade.user');

        $type = $request->get('type');
        $title = null;
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
                $title = $this->trans('finalwork.text.author_list');
                break;
            case WorkUserTypeConstant::OPPONENT:
                $title = $this->trans('finalwork.text.opponent_list');
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $title = $this->trans('finalwork.text.consultant_list');
                break;
            default:
                $showSearchTab = false;
                $getUserWorkAndStatus = false;
                $usersQuery = $userService->queryUnusedUsers($user);
                $title = $this->trans('finalwork.text.unused_user_list');
                break;
        }

        $pagination = $this->createPagination($request, $usersQuery);
        $works = new ArrayCollection;
        $userStatusWorkCounts = new ArrayCollection;

        if ($getUserWorkAndStatus === true) {
            foreach ($pagination as $paginationUser) {
                $paginationUserWorks = $this->get('final_work.facade.work')
                    ->getWorksByUserStatus($paginationUser, $user, $type, $workStatus);

                if ($works->get($paginationUser->getId()) === null) {
                    $works->set($paginationUser->getId(), $paginationUserWorks);
                }

                $workStatusCount = $this->get('final_work.facade.work_status')
                    ->getCountByUser($paginationUser, $user, $type, $workStatus);

                if ($userStatusWorkCounts->get($paginationUser->getId()) === null) {
                    $userStatusWorkCounts->set($paginationUser->getId(), $workStatusCount);
                }
            }
        }

        $this->get('final_work.seo_page')->setTitle($title);

        return $this->render('@FinalWork/user/user_list.html.twig', [
            'type' => $type,
            'title' => $title,
            'users' => $pagination,
            'userWorks' => $works,
            'userStatusWorkCounts' => $userStatusWorkCounts,
            'form' => $form->createView(),
            'openSearchTab' => $openSearchTab,
            'showSearchTab' => $showSearchTab,
            'userHelper' => new UserHelper()
        ]);
    }

    /**
     * @param string $type
     * @param UserModel $userModel
     * @param User $user
     * @return FormInterface
     */
    public function getUserForm(
        string $type,
        UserModel $userModel,
        User $user = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('user_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('user_edit_ajax', [
                        'id' => $this->hashIdEncode($user->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                ];
                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->createForm(UserForm::class, $userModel, $parameters);
    }
}
