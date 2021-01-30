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
use App\Entity\MediaCategory;
use App\Form\MediaCategoryForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\MediaCategory\MediaCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class DocumentCategoryController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->getUser();

        $form = $this->createForm(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.media_category')->flushFromModel($mediaCategoryModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        MediaCategory $mediaCategory
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);
        $form = $this->createForm(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.media_category')
                ->flushFromModel($mediaCategoryModel, $mediaCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(MediaCategory $mediaCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        if (count($mediaCategory->getMedias()) === 0) {
            $this->removeEntity($mediaCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
    }
}
