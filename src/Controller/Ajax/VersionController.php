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
    VoterSupportConstant
};
use App\Controller\MediaBaseController;
use App\Form\VersionForm;
use App\Security\Voter\Subject\VersionVoterSubject;
use App\Entity\{
    Work,
    Media,
    MediaType
};
use App\Helper\FormValidationMessageHelper;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VersionController extends MediaBaseController
{
    public function create(
        Request $request,
        Work $work
    ): JsonResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->denyAccessUnlessGranted(VoterSupportConstant::CREATE, $versionVoterSubject);

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION);

        $form = $this
            ->createForm(VersionForm::class, $mediaModel, [
                'uploadMedia' => true,
                'mimeTypes' => $this->get('app.facade.media.mime_type')->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->get('app.factory.media')
                ->flushFromModel($mediaModel);

            $this->get('app.event_dispatcher.version')
                ->onVersionCreate($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\Media", options={"id" = "id_media"})
     */
    public function edit(
        Request $request,
        Work $work,
        Media $media
    ): Response {
        $versionVoterSubject = (new VersionVoterSubject)
            ->setWork($work)
            ->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $versionVoterSubject);

        $mediaModel = MediaModel::fromMedia($media);
        $form = $this
            ->createForm(VersionForm::class, $mediaModel, [
                'mimeTypes' => $this->get('app.facade.media.mime_type')->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.factory.media')
                ->flushFromModel($mediaModel, $media);

            $media->setOwner($this->getUser());
            $this->get('app.event_dispatcher.version')
                ->onVersionEdit($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\Media", options={"id" = "id_media"})
     */
    public function delete(
        Work $work,
        Media $media
    ): JsonResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $versionVoterSubject);

        $this->removeEntity($media);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
