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
use FinalWork\FinalWorkBundle\Model\MediaCategory\MediaCategoryModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\FinalWorkBundle\Form\MediaCategoryForm;
use LogicException;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class DocumentCategoryController extends BaseController
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->getUser();

        $form = $this->getDocumentCategoryForm(ControllerMethodConstant::CREATE, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.media_category')
                    ->flushFromModel($mediaCategoryModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('document_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentCategoryForm(ControllerMethodConstant::CREATE_AJAX, $mediaCategoryModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_materials_category_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/document_category/document_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.information_materials_category_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws LogicException
     */
    public function listAction(Request $request): Response
    {
        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_materials_category_list');

        return $this->render('@FinalWork/document_category/list.html.twig', [
            'mediaCategories' => $this->createPagination(
                $request,
                $this->get('final_work.facade.media_category')->queryMediaCategoriesByOwner($this->getUser()),
                $this->getParam('pagination.default.page'),
                $this->getParam('pagination.document_category.limit')
            )
        ]);
    }

    /**
     * @param Request $request
     * @param MediaCategory $mediaCategory
     * @return Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        MediaCategory $mediaCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);
        $form = $this->getDocumentCategoryForm(ControllerMethodConstant::EDIT, $mediaCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.media_category')
                    ->flushFromModel($mediaCategoryModel, $mediaCategory);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('document_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getDocumentCategoryForm(
                ControllerMethodConstant::EDIT_AJAX,
                $mediaCategoryModel,
                $mediaCategory
            );
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.information_materials_category_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/document_category/document_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.information_materials_category_edit'),
            'mediaCategory' => $mediaCategory,
            'buttonActionTitle' => $this->trans('finalwork.form.action.edit'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.edit_and_close')
        ]);
    }

    /**
     * @param MediaCategory $mediaCategory
     * @return RedirectResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(MediaCategory $mediaCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        if (count($mediaCategory->getMedias()) === 0) {
            $this->removeEntity($mediaCategory);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.delete.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');
        }

        return $this->redirectToRoute('document_category_list');
    }

    /**
     * @param string $type
     * @param MediaCategoryModel $mediaCategoryModel
     * @param MediaCategory $mediaCategory
     * @return FormInterface
     */
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
