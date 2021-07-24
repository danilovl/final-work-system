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

use App\Model\MediaCategory\MediaCategoryModel;
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class DocumentCategoryController extends BaseController
{
    public function create(Request $request): Response
    {
        $documentCategoryFormFactory = $this->get('app.form_factory.document_category');

        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->getUser();

        $form = $documentCategoryFormFactory->getDocumentCategoryForm(
            ControllerMethodConstant::CREATE,
            $mediaCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.media_category')
                    ->flushFromModel($mediaCategoryModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('document_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $documentCategoryFormFactory->getDocumentCategoryForm(
                ControllerMethodConstant::CREATE_AJAX,
                $mediaCategoryModel
            );
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'document_category/document_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.information_materials_category_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function list(Request $request): Response
    {
        return $this->render('document_category/list.html.twig', [
            'mediaCategories' => $this->createPagination(
                $request,
                $this->get('app.facade.media_category')->queryMediaCategoriesByOwner($this->getUser()),
                $this->getParam('pagination.default.page'),
                $this->getParam('pagination.document_category.limit')
            )
        ]);
    }

    public function edit(
        Request $request,
        MediaCategory $mediaCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        $documentCategoryFormFactory = $this->get('app.form_factory.document_category');
        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);

        $form = $documentCategoryFormFactory->getDocumentCategoryForm(
            ControllerMethodConstant::EDIT,
            $mediaCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.media_category')
                    ->flushFromModel($mediaCategoryModel, $mediaCategory);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('document_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $documentCategoryFormFactory->getDocumentCategoryForm(
                ControllerMethodConstant::EDIT_AJAX,
                $mediaCategoryModel,
                $mediaCategory
            );
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'document_category/document_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.information_materials_category_edit'),
            'mediaCategory' => $mediaCategory,
            'buttonActionTitle' => $this->trans('app.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.edit_and_close')
        ]);
    }

    public function delete(MediaCategory $mediaCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        if (count($mediaCategory->getMedias()) === 0) {
            $this->removeEntity($mediaCategory);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->redirectToRoute('document_category_list');
    }
}
