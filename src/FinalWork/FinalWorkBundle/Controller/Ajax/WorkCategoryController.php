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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\FinalWorkBundle\Form\WorkCategoryForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use FinalWork\FinalWorkBundle\Model\WorkCategory\WorkCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkCategoryController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->getUser();

        $form = $this->createForm(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.work_category')
                ->flushFromModel($workCategoryModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param WorkCategory $workCategory
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        WorkCategory $workCategory
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->createForm(WorkCategoryForm::class, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.work_category')
                ->flushFromModel($workCategoryModel, $workCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param WorkCategory $workCategory
     * @return JsonResponse
     */
    public function deleteAction(WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        if (count($workCategory->getWorks()) > 0) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
        }

        $this->removeEntity($workCategory);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
