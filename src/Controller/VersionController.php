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

namespace App\Controller;

use App\Exception\{
    RuntimeException,
    ConstantNotFoundException
};
use App\Model\Media\MediaModel;
use App\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    MediaTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Form\VersionForm;
use App\Security\Voter\Subject\VersionVoterSubject;
use App\Entity\{
    Work,
    Media,
    MediaType
};
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    BinaryFileResponse
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class VersionController extends MediaBaseController
{
    public function create(
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
                $media = $this->get('app.factory.media')
                    ->flushFromModel($mediaModel);

                $this->get('app.event_dispatcher.version')
                    ->onVersionCreate($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getVersionForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel, null, $work);
        }

        $this->get('app.seo_page')->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, 'version/version.html.twig'), [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.version_add'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
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
        $form = $this->getVersionForm(ControllerMethodConstant::EDIT, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.media')
                    ->flushFromModel($mediaModel, $media);

                $media->setOwner($this->getUser());
                $this->get('app.event_dispatcher.version')
                    ->onVersionEdit($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('work_detail', [
                    'id' => $this->hashIdEncode($work->getId())
                ]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getVersionForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media, $work);
        }

        $this->get('app.seo_page')->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        return $this->render($this->ajaxOrNormalFolder($request, 'version/version.html.twig'), [
            'work' => $work,
            'media' => $media,
            'form' => $form->createView(),
            'title' => $this->trans('app.page.version_edit'),
            'buttonActionTitle' => $this->trans('app.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function detailContent(Media $version): Response
    {
        $versionVoterSubject = (new VersionVoterSubject)->setMedia($version);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $versionVoterSubject, 'The work media does not exist');

        return $this->render('version/detail_content.html.twig', [
            'version' => $version,
            'work' => $version->getWork()
        ]);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\media", options={"id" = "id_media"})
     */
    public function download(
        Work $work,
        Media $media
    ): BinaryFileResponse {
        $versionVoterSubject = new VersionVoterSubject;
        $versionVoterSubject->setWork($work);
        $versionVoterSubject->setMedia($media);

        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $versionVoterSubject);

        return $this->downloadMedia($media);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("media", class="App\Entity\media", options={"id" = "id_media"})
     */
    public function downloadGoogle(
        Work $work,
        Media $media
    ): BinaryFileResponse {
        return $this->downloadMedia($media);
    }

    public function getVersionForm(
        string $type,
        MediaModel $mediaModel,
        ?Media $media = null,
        Work $work = null
    ): FormInterface {
        $mimeTypes = $this->get('app.facade.media.mime_type')
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
                    'uploadMedia' => true
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
                    'method' => Request::METHOD_POST
                ]);
                break;
            default:
                throw new ConstantNotFoundException('Controller method type not found');
        }

        return $this->createForm(VersionForm::class, $mediaModel, $parameters);
    }
}
