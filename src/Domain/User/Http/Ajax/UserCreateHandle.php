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

use App\Application\Constant\{
    AjaxJsonTypeConstant,
    FlashTypeConstant};
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\RequestService;
use App\Domain\User\Bus\Command\CreateUser\CreateUserCommand;
use App\Domain\User\Form\UserForm;
use App\Domain\User\Model\UserModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request};

readonly class UserCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $userModel = new UserModel;

        $form = $this->formFactory
            ->create(UserForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateUserCommand::create($userModel);
            $user = $this->commandBus->dispatchResult($command);

            if ($user === null) {
                return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                    'data' => FormValidationMessageHelper::getErrorMessages($form)
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.user.create.success');

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
