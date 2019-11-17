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

namespace FinalWork\FinalWorkBundle\Controller;

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use FinalWork\FinalWorkBundle\Model\Media\MediaModel;
use FinalWork\FinalWorkBundle\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    MediaTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Form\VersionForm;
use FinalWork\FinalWorkBundle\Security\Voter\Subject\VersionVoterSubject;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Media,
    MediaType
};
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VersionController extends MediaBaseController
{
    /**
     * @param Request $request
     * @param Work $work
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(
        Request $request,
        Work $work
    ): Response {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);

        $this->denyAccessUnlessGranted(VoterSupportConstant::CREATE, $versionVoterSubject);

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION);

        $form = $this->getVersionForm(ControllerMethodConstant::CREATE, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $media = $this->get('final_work.factory.media')
                    ->flushFromModel($mediaModel);

                $this->get('final_work.event_dispatcher.version')
                    ->onVersionCreate($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getVersionForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel, null, $work);
        }

        $this->get('final_work.seo_page')
            ->setTitle('finalwork.page.version_add')
            ->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/version/version.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.version_add'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
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
        $form = $this->getVersionForm(ControllerMethodConstant::EDIT, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.media')
                    ->flushFromModel($mediaModel, $media);

                $media->setOwner($this->getUser());
                $this->get('final_work.event_dispatcher.version')
                    ->onVersionEdit($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getVersionForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media, $work);
        }

        $this->get('final_work.seo_page')
            ->setTitle('finalwork.page.version_edit')
            ->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/version/version.html.twig'), [
            'work' => $work,
            'media' => $media,
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.version_edit'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param Media $version
     * @return Response
     * @throws NotFoundHttpException
     */
    public function detailContentAction(Media $version): Response
    {
        $versionVoterSubject = (new VersionVoterSubject)
            ->setMedia($version);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $versionVoterSubject, 'The work media does not exist');

        return $this->render('@FinalWork/version/detail_content.html.twig', [
            'version' => $version,
            'work' => $version->getWork()
        ]);
    }

    /**
     * @param Work $work
     * @param Media $media
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="FinalWork\FinalWorkBundle\Entity\media", options={"id" = "id_media"})
     *
     * @return Response
     */
    public function downloadAction(
        Work $work,
        Media $media
    ): Response {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $versionVoterSubject);

        $this->download($media);

        return new Response;
    }

    /**
     * @param Work $work
     * @param Media $media
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="FinalWork\FinalWorkBundle\Entity\media", options={"id" = "id_media"})
     *
     * @return Response
     */
    public function downloadGoogleAction(
        Work $work,
        Media $media
    ): Response {
        $this->download($media);

        return new Response;
    }

    /**
     * @param string $type
     * @param MediaModel $mediaModel
     * @param Media $media
     * @param Work|null $work
     * @return FormInterface
     */
    public function getVersionForm(
        string $type,
        MediaModel $mediaModel,
        ?Media $media = null,
        Work $work = null
    ): FormInterface {
        $mimeTypes = $this->get('final_work.facade.media.mime_type')
            ->getFormValidationMimeTypes(true);

        $parameters = [
            'mimeTypes' => $mimeTypes
        ];

        switch ($type) {
            case ControllerMethodConstant::CREATE:
                $parameters['uploadMedia'] = true;
                break;
            case ControllerMethodConstant::EDIT:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = array_merge($parameters, [
                    'action' => $this->generateUrl('version_create_ajax', [
                        'id' => $this->hashIdEncode($work->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                    'uploadMedia' => true,
                ]);
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                if ($work === null) {
                    throw new RuntimeException('Work must not be null for edit ajax');
                }

                $parameters = array_merge($parameters, [
                    'action' => $this->generateUrl('version_edit_ajax', [
                        'id_work' => $this->hashIdEncode($work->getId()),
                        'id_media' => $this->hashIdEncode($media->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                ]);
                break;
            default:
                throw new ConstantNotFoundException('Controller method type not found');
        }

        return $this->createForm(VersionForm::class, $mediaModel, $parameters);
    }
}
