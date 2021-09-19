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

namespace App\Model\WorkCategory\Http\Ajax;

use App\Entity\WorkCategory;
use App\Form\WorkCategoryForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\WorkCategory\Factory\WorkCategoryFactory;
use App\Model\WorkCategory\WorkCategoryModel;
use App\Constant\AjaxJsonTypeConstant;
use App\Service\{
    RequestService,
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Form\FormFactoryInterface;

class WorkCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private WorkCategoryFactory $workCategoryFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request, WorkCategory $workCategory): JsonResponse
    {
        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->formFactory
            ->create(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workCategoryFactory
                ->flushFromModel($workCategoryModel, $workCategory);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
