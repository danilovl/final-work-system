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
use Exception;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    AjaxJsonTypeConstant
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Form\UserForm;
use FinalWork\FinalWorkBundle\Model\User\UserModel;
use FinalWork\FinalWorkBundle\Helper\{
    FunctionHelper,
    FormValidationMessageHelper
};
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use FOS\UserBundle\Model\UserManagerInterface;

class UserController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createAction(Request $request): JsonResponse
    {
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $userModel = new UserModel;

        $form = $this->createForm(UserForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $username = $form->get('username')->getData();
            if ($userManager->findUserByUsername($username) || $userManager->findUserByEmail($email)) {
                return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                    'data' => FormValidationMessageHelper::getErrorMessages($form)
                ]);
            }

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

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        User $user
    ): JsonResponse {
        $userModel = UserModel::fromUser($user);
        $form = $this->createForm(UserForm::class, $userModel)
            ->remove('username')
            ->remove('role')
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.user')
                ->flushFromModel($userModel, $user);

            $this->get('final_work.event_dispatcher.user')
                ->onUserEdit($user);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
