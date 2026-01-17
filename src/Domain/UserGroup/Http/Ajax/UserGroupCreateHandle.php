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

namespace App\Domain\UserGroup\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService
};
use App\Domain\UserGroup\Bus\Command\CreateUserGroup\CreateUserGroupCommand;
use App\Domain\UserGroup\Form\UserGroupForm;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class UserGroupCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TranslatorService $translatorService,
        private CommandBusInterface $commandBus,
        private FormFactoryInterface $formFactory
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = $this->translatorService->trans('app.text.name');

        $form = $this->formFactory
            ->create(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateUserGroupCommand::create($userGroupModel);
            $this->commandBus->dispatch($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
