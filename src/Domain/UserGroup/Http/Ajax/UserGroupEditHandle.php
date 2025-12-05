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
use App\Application\Service\RequestService;
use App\Domain\UserGroup\Bus\Command\EditUserGroup\EditUserGroupCommand;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Form\UserGroupForm;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class UserGroupEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Group $group): JsonResponse
    {
        $userGroupModel = UserGroupModel::fromGroup($group);
        $form = $this->formFactory
            ->create(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = EditUserGroupCommand::create($userGroupModel, $group);
            $this->commandBus->dispatchResult($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
