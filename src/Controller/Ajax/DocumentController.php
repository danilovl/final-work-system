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

use App\Model\Media\MediaModel;
use App\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Controller\MediaBaseController;
use App\Entity\{
    Media,
    MediaType
};
use App\Helper\FormValidationMessageHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

class DocumentController extends MediaBaseController
{
    public function create(Request $request): JsonResponse
    {
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL);

        $form = $this->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->get('app.factory.media')
                ->flushFromModel($mediaModel);

            $this->get('app.event_dispatcher.document')
                ->onDocumentCreate($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function edit(
        Request $request,
        Media $media
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.media')
                ->flushFromModel($mediaModel, $media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function changeActive(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        $media->changeActive();
        $this->flushEntity($media);

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    public function delete(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $media);

        $this->removeEntity($media);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }

    public function getDocumentForm(
        string $type,
        MediaModel $mediaModel = null,
        Media $media = null
    ): FormInterface {
        return $this->get('app.document_form')
            ->setUser($this->getUser())
            ->getDocumentForm($type, $mediaModel, $media);
    }
}
