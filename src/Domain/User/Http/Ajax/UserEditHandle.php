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

namespace App\Domain\User\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\RequestService;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\UserEventDispatcher;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Form\UserEditForm;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class UserEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private UserFactory $userFactory,
        private UserEventDispatcher $userEventDispatcher
    ) {}

    public function __invoke(Request $request, User $user): JsonResponse
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

            $this->userEventDispatcher->onUserEdit(
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
