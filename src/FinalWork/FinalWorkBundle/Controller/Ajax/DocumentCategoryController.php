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
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\FinalWorkBundle\Form\MediaCategoryForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use FinalWork\FinalWorkBundle\Model\MediaCategory\MediaCategoryModel;
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class DocumentCategoryController extends BaseController
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
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->getUser();

        $form = $this->createForm(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.media_category')
                ->flushFromModel($mediaCategoryModel);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param MediaCategory $mediaCategory
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        MediaCategory $mediaCategory
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);
        $form = $this->createForm(MediaCategoryForm::class, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.media_category')
                ->flushFromModel($mediaCategoryModel, $mediaCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param MediaCategory $mediaCategory
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function deleteAction(MediaCategory $mediaCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        if (count($mediaCategory->getMedias()) === 0) {
            $this->removeEntity($mediaCategory);

            return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
    }
}
