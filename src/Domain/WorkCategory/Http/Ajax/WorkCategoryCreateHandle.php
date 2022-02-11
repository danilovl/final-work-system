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

namespace App\Domain\WorkCategory\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\{
    UserService,
    RequestService
};
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;
use App\Domain\WorkCategory\Form\WorkCategoryForm;
use App\Domain\WorkCategory\WorkCategoryModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class WorkCategoryCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private WorkCategoryFactory $workCategoryFactory
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->userService->getUser();

        $form = $this->formFactory
            ->create(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workCategoryFactory->flushFromModel($workCategoryModel);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
