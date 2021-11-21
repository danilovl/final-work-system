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

use App\Model\User\Entity\User;
use App\Model\User\EventDispatcher\UserEventDispatcherService;
use App\Model\User\Form\UserEditForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\User\Factory\UserFactory;
use App\Model\User\UserModel;
use App\Constant\AjaxJsonTypeConstant;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService

};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private UserFactory $userFactory,
        private UserEventDispatcherService $userEventDispatcherService
    ) {
    }

    public function handle(Request $request, User $user): JsonResponse
    {
        $userModel = UserModel::fromUser($user);
        $form = $this->formFactory
            ->create(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userFactory->flushFromModel(
                $userModel,
                $user
            );

            $this->userEventDispatcherService->onUserEdit(
                $user,
                $this->userService->getUser()
            );

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
