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
use FinalWork\FinalWorkBundle\Model\UserGroup\UserGroupModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Form\UserGroupForm;
use FinalWork\SonataUserBundle\Entity\Group;
use LogicException;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = 'Name';

        $form = $this->getUserGroupForm(ControllerMethodConstant::CREATE, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $this->get('final_work.factory.user_group')->flushFromModel($userGroupModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('user_group_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getUserGroupForm(ControllerMethodConstant::CREATE_AJAX, $userGroupModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.user_group_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/user_group/user_group.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.user_group_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @param Group $group
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Group $group
    ): Response {
        $userGroupModel = UserGroupModel::fromGroup($group);
        $form = $this->getUserGroupForm(ControllerMethodConstant::EDIT, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.user_group')
                    ->flushFromModel($userGroupModel, $group);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('user_group_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getUserGroupForm(ControllerMethodConstant::EDIT_AJAX, $userGroupModel, $group);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.user_group_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/user_group/user_group.html.twig'), [
            'group' => $group,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.user_group_edit'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws LogicException
     */
    public function listAction(Request $request): Response
    {
        $this->get('final_work.seo_page')->setTitle('finalwork.page.user_group_list');

        return $this->render('@FinalWork/user_group/list.html.twig', [
            'groups' => $this->createPagination(
                $request,
                $this->get('final_work.facade.user_group')->queryAll()
            ),
        ]);
    }

    /**
     * @param string $type
     * @param UserGroupModel $userGroupModel
     * @param Group $group
     * @return FormInterface
     */
    public function getUserGroupForm(
        string $type,
        UserGroupModel $userGroupModel,
        Group $group = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('user_group_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('user_group_edit_ajax', [
                        'id' => $this->hashIdEncode($group->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->createForm(UserGroupForm::class, $userGroupModel, $parameters);
    }
}
