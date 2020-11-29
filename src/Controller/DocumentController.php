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

use App\Model\Media\MediaModel;
use App\Constant\{
    FlashTypeConstant,
    MediaTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\{
    Media,
    MediaType
};
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentController extends MediaBaseController
{
    public function create(Request $request): Response
    {
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL);

        $form = $this->getDocumentForm(ControllerMethodConstant::CREATE, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $media = $this->get('app.factory.media')
                    ->flushFromModel($mediaModel);

                $this->get('app.event_dispatcher.document')
                    ->onDocumentCreate($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('document_list_owner');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'document/document.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.information_material_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function detailContent(Media $media): Response
    {
        return $this->render('document/detail_content.html.twig', [
            'document' => $media
        ]);
    }

    public function edit(
        Request $request,
        Media $media
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->getDocumentForm(ControllerMethodConstant::EDIT, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.media')
                    ->flushFromModel($mediaModel, $media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('document_edit', ['id' => $media->getId()]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'document/document.html.twig'), [
            'media' => $media,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->trans('app.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function list(Request $request): Response
    {
        $openSearchTab = false;
        $criteria = null;

        $form = $this->getDocumentForm(ControllerMethodConstant::LIST)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        $documents = $this->get('app.facade.media')->getMediaListQueryByUserFilter(
            $this->get('app.facade.user')->getAllUserActiveSupervisors($this->getUser()),
            $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            true,
            $criteria
        );

        return $this->render('document/list.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->createPagination($request, $documents),
            'form' => $form->createView()
        ]);
    }

    public function listOwner(Request $request): Response
    {
        $user = $this->getUser();
        $openSearchTab = false;
        $criteria = null;

        $form = $this->getDocumentForm(ControllerMethodConstant::LIST_OWNER)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        $documents = $this->get('app.facade.media')->getMediaListQueryByUserFilter(
            $user,
            $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            null,
            $criteria
        );

        return $this->render('document/list_owner.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->createPagination($request, $documents),
            'form' => $form->createView()
        ]);
    }

    public function download(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->downloadMedia($media);

        return new Response;
    }

    public function downloadGoogle(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->downloadMedia($media);

        return new Response;
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