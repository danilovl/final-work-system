<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\User\Http\Ajax;

use App\EventDispatcher\UserEventDispatcherService;
use App\Form\UserForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\User\Facade\UserFacade;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use App\Constant\{
    FlashTypeConstant,
    AjaxJsonTypeConstant
};
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Form\FormFactoryInterface;

class UserCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserFacade $userFacade,
        private FormFactoryInterface $formFactory,
        private UserFactory $userFactory,
        private UserEventDispatcherService $userEventDispatcherService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $userModel = new UserModel;

        $form = $this->formFactory
            ->create(UserForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $userModel->email;
            $username = $userModel->username;

            if ($this->userFacade->findOneByUsername($username) || $this->userFacade->findOneByEmail($email)) {
                return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                    'data' => FormValidationMessageHelper::getErrorMessages($form)
                ]);
            }

            $newUser = $this->userFactory->createNewUser($userModel);
            $this->userEventDispatcherService->onUserCreate($newUser);

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.user.create.success');

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
