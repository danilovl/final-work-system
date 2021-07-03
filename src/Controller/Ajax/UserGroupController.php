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

namespace App\Controller\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Controller\BaseController;
use App\Form\UserGroupForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\UserGroup\UserGroupModel;
use App\Entity\Group;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserGroupController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = $this->trans('app.text.name');

        $form = $this->createForm(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.user_group')
                ->flushFromModel($userGroupModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        Group $group
    ): JsonResponse {
        $userGroupModel = UserGroupModel::fromGroup($group);
        $form = $this->createForm(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.user_group')
                ->flushFromModel($userGroupModel, $group);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(Group $group): JsonResponse
    {
        $this->removeEntity($group);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
