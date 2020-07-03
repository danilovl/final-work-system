<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller\Ajax;

use App\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Controller\BaseController;
use App\Entity\WorkCategory;
use App\Form\WorkCategoryForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\WorkCategory\WorkCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkCategoryController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->getUser();

        $form = $this->createForm(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.work_category')
                ->flushFromModel($workCategoryModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        WorkCategory $workCategory
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->createForm(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.work_category')
                ->flushFromModel($workCategoryModel, $workCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        if (count($workCategory->getWorks()) > 0) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
        }

        $this->removeEntity($workCategory);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
