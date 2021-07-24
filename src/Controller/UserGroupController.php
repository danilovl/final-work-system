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

use App\Model\UserGroup\UserGroupModel;
use App\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Entity\Group;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupController extends BaseController
{
    public function create(Request $request): Response
    {
        $userGroupFormFactory = $this->get('app.form_factory.user_group');

        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = 'Name';

        $form = $userGroupFormFactory->getUserGroupForm(
            ControllerMethodConstant::CREATE,
            $userGroupModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.user_group')
                    ->flushFromModel($userGroupModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('user_group_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::CREATE_AJAX,
                $userGroupModel
            );
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'user_group/user_group.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.user_group_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function edit(
        Request $request,
        Group $group
    ): Response {
        $userGroupFormFactory = $this->get('app.form_factory.user_group');
        $userGroupModel = UserGroupModel::fromGroup($group);

        $form = $userGroupFormFactory->getUserGroupForm(
            ControllerMethodConstant::EDIT,
            $userGroupModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.user_group')
                    ->flushFromModel($userGroupModel, $group);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('user_group_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $userGroupFormFactory->getUserGroupForm(
                ControllerMethodConstant::EDIT_AJAX,
                $userGroupModel,
                $group
            );
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'user_group/user_group.html.twig'), [
            'group' => $group,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.user_group_edit'),
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function list(Request $request): Response
    {
        $this->get('app.seo_page')->setTitle('app.page.user_group_list');

        return $this->render('user_group/list.html.twig', [
            'groups' => $this->createPagination(
                $request,
                $this->get('app.facade.user_group')->queryAll()
            ),
        ]);
    }
}
