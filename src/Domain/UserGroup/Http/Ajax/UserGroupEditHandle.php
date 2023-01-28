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
use App\Application\Service\RequestService;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Factory\UserGroupFactory;
use App\Domain\UserGroup\Form\UserGroupForm;
use App\Domain\UserGroup\UserGroupModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class UserGroupEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserGroupFactory $userGroupFactory,
        private FormFactoryInterface $formFactory
    ) {}

    public function handle(Request $request, Group $group): JsonResponse
    {
        $userGroupModel = UserGroupModel::fromGroup($group);
        $form = $this->formFactory
            ->create(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userGroupFactory->flushFromModel($userGroupModel, $group);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
