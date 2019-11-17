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
use FinalWork\FinalWorkBundle\Constant\AjaxJsonTypeConstant;
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Form\UserGroupForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use FinalWork\FinalWorkBundle\Model\UserGroup\UserGroupModel;
use FinalWork\SonataUserBundle\Entity\Group;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserGroupController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = 'Name';

        $form = $this->createForm(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.user_group')
                ->flushFromModel($userGroupModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Group $group
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Group $group
    ): JsonResponse {
        $userGroupModel = UserGroupModel::fromGroup($group);
        $form = $this->createForm(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.user_group')
                ->flushFromModel($userGroupModel, $group);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Group $group
     * @return JsonResponse
     */
    public function deleteAction(Group $group): JsonResponse
    {
        $this->removeEntity($group);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
