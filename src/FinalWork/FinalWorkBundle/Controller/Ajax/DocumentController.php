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

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Model\Media\MediaModel;
use FinalWork\FinalWorkBundle\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Controller\MediaBaseController;
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaType
};
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class DocumentController extends MediaBaseController
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
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL);

        $form = $this->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->get('final_work.factory.media')
                ->flushFromModel($mediaModel);

            $this->get('final_work.event_dispatcher.document')
                ->onDocumentCreate($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Media $media
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        Media $media
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.media')
                ->flushFromModel($mediaModel, $media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Media $media
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function changeActiveAction(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        $media->changeActive();
        $this->flushEntity();

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    /**
     * @param Media $media
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function deleteAction(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $media);

        $this->removeEntity($media);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }

    /**
     * @param string $type
     * @param MediaModel|null $mediaModel
     * @param Media|null $media
     * @return FormInterface
     */
    public function getDocumentForm(
        string $type,
        MediaModel $mediaModel = null,
        Media $media = null
    ): FormInterface {
        return $this->get('final_work.document_form')
            ->setUser($this->getUser())
            ->getDocumentForm($type, $mediaModel, $media);
    }
}
