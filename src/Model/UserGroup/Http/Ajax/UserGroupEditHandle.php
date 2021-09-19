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

namespace App\Model\UserGroup\Http\Ajax;

use App\Entity\Group;
use App\Form\UserGroupForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\UserGroup\Factory\UserGroupFactory;
use App\Model\UserGroup\UserGroupModel;
use App\Constant\AjaxJsonTypeConstant;
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Form\FormFactoryInterface;

class UserGroupEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserGroupFactory $userGroupFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

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
