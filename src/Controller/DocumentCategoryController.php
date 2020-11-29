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
use App\Form\MediaCategoryForm;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class DocumentCategoryController extends BaseController
{
    public function create(Request $request): Response
    {
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->getUser();

        $form = $this->getDocumentCategoryForm(ControllerMethodConstant::CREATE, $mediaCategoryModel)
            ->handleRequest($request);

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
            $form = $this->getDocumentCategoryForm(ControllerMethodConstant::CREATE_AJAX, $mediaCategoryModel);
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

        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);
        $form = $this->getDocumentCategoryForm(ControllerMethodConstant::EDIT, $mediaCategoryModel)
            ->handleRequest($request);

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
            $form = $this->getDocumentCategoryForm(
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

    public function getDocumentCategoryForm(
        string $type,
        MediaCategoryModel $mediaCategoryModel,
        MediaCategory $mediaCategory = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('document_category_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('document_category_edit_ajax', [
                        'id' => $this->hashIdEncode($mediaCategory->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->createForm(MediaCategoryForm::class, $mediaCategoryModel, $parameters);
    }
}
