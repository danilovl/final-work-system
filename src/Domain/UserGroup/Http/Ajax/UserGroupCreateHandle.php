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
use App\Application\Service\{
    RequestService,
    TranslatorService
};
use App\Domain\UserGroup\Factory\UserGroupFactory;
use App\Domain\UserGroup\Form\UserGroupForm;
use App\Domain\UserGroup\UserGroupModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class UserGroupCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private TranslatorService $translatorService,
        private UserGroupFactory $userGroupFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $userGroupModel = new UserGroupModel;
        $userGroupModel->name = $this->translatorService->trans('app.text.name');

        $form = $this->formFactory
            ->create(UserGroupForm::class, $userGroupModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userGroupFactory->flushFromModel($userGroupModel);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
