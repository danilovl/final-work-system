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
use Exception;
use FinalWork\FinalWorkBundle\Model\Media\MediaModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    MediaTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaType
};
use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentController extends MediaBaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->getUser();
        $mediaModel->type = $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL);

        $form = $this->getDocumentForm(ControllerMethodConstant::CREATE, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $media = $this->get('final_work.factory.media')
                    ->flushFromModel($mediaModel);

                $this->get('final_work.event_dispatcher.document')
                    ->onDocumentCreate($media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('document_list_owner');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_material_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/document/document.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.information_material_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Media $media
     * @return Response
     */
    public function detailContentAction(Media $media): Response
    {
        return $this->render('@FinalWork/document/detail_content.html.twig', [
            'document' => $media
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
        $form = $this->getDocumentForm(ControllerMethodConstant::EDIT, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.media')
                    ->flushFromModel($mediaModel, $media);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('document_edit', ['id' => $media->getId()]);
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_material_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/document/document.html.twig'), [
            'media' => $media,
            'form' => $form->createView(),
            'buttonActionTitle' => $this->trans('finalwork.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     */
    public function listAction(Request $request): Response
    {
        $openSearchTab = false;
        $criteria = null;

        $form = $this->getDocumentForm(ControllerMethodConstant::LIST)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $openSearchTab = true;
            $criteria = $form->getData();
        }

        $documents = $this->get('final_work.facade.media')->getMediaListQueryByUserFilter(
            $this->get('final_work.facade.user')->getAllUserActiveSupervisors($this->getUser()),
            $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            true,
            $criteria
        );

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_materials');

        return $this->render('@FinalWork/document/list.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->createPagination($request, $documents),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     */
    public function listOwnerAction(Request $request): Response
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

        $documents = $this->get('final_work.facade.media')->getMediaListQueryByUserFilter(
            $user,
            $this->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL),
            null,
            $criteria
        );

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_materials');

        return $this->render('@FinalWork/document/list_owner.html.twig', [
            'openSearchTab' => $openSearchTab,
            'documents' => $this->createPagination($request, $documents),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Media $media
     * @return Response
     *
     * @throws Exception
     */
    public function downloadAction(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->download($media);

        return new Response;
    }

    /**
     * @param Media $media
     * @return Response
     *
     * @throws Exception
     */
    public function downloadGoogleAction(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->download($media);

        return new Response;
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