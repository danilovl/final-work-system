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
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Controller\MediaBaseController;
use FinalWork\FinalWorkBundle\Form\VersionForm;
use FinalWork\FinalWorkBundle\Security\Voter\Subject\VersionVoterSubject;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Media,
    MediaType
};
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VersionController extends MediaBaseController
{
    /**
     * @param Request $request
     * @param Work $work
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(
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
                'mimeTypes' => $this->get('final_work.facade.media.mime_type')->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->get('final_work.factory.media')
                ->flushFromModel($mediaModel);

            $this->get('final_work.event_dispatcher.version')
                ->onVersionCreate($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Request $request
     * @param Work $work
     * @param Media $media
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="FinalWork\FinalWorkBundle\Entity\Media", options={"id" = "id_media"})
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     *
     */
    public function editAction(
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
                'mimeTypes' => $this->get('final_work.facade.media.mime_type')->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('final_work.factory.media')
                ->flushFromModel($mediaModel, $media);

            $media->setOwner($this->getUser());
            $this->get('final_work.event_dispatcher.version')
                ->onVersionEdit($media);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Work $work
     * @param Media $media
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="FinalWork\FinalWorkBundle\Entity\Media", options={"id" = "id_media"})
     *
     * @return JsonResponse
     */
    public function deleteAction(
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
