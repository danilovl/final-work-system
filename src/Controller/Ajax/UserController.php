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

use App\Constant\{
    FlashTypeConstant,
    AjaxJsonTypeConstant
};
use App\Controller\BaseController;
use App\Form\{
    UserForm,
    UserEditForm
};
use App\Model\User\UserModel;
use App\Helper\FormValidationMessageHelper;
use App\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $userFacade = $this->get('app.facade.user');
        $userModel = new UserModel;

        $form = $this->createForm(UserForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $userModel->email;
            $username = $userModel->username;

            if ($userFacade->findOneByUsername($username) || $userFacade->findOneByEmail($email)) {
                return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                    'data' => FormValidationMessageHelper::getErrorMessages($form)
                ]);
            }

            $newUser = $this->get('app.factory.user')->createNewUser($userModel);

            $this->get('app.event_dispatcher.user')
                ->onUserCreate($newUser);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.user.create.success');

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        User $user
    ): JsonResponse {
        $userModel = UserModel::fromUser($user);
        $form = $this->createForm(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.user')
                ->flushFromModel($userModel, $user);

            $this->get('app.event_dispatcher.user')
                ->onUserEdit($user, $this->getUser());

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
