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
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use FinalWork\FinalWorkBundle\Model\WorkCategory\WorkCategoryModel;
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\FinalWorkBundle\Form\WorkCategoryForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkCategoryController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): Response
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->getUser();

        $form = $this->getWorkCategoryForm(ControllerMethodConstant::CREATE, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.work_category')
                    ->flushFromModel($workCategoryModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('work_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getWorkCategoryForm(ControllerMethodConstant::CREATE_AJAX, $workCategoryModel);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.work_category_create');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/work_category/work_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('finalwork.page.work_category_create'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.create_and_close')
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $this->get('final_work.seo_page')->setTitle('finalwork.page.work_category_list');

        return $this->render('@FinalWork/work_category/list.html.twig', [
            'workCategories' => $this->createPagination(
                $request,
                $this->get('final_work.facade.work.category')->queryWorkCategoriesByOwner($this->getUser())
            )
        ]);
    }

    /**
     * @param Request $request
     * @param WorkCategory $workCategory
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Request $request,
        WorkCategory $workCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->getWorkCategoryForm(ControllerMethodConstant::EDIT, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('final_work.factory.work_category')
                    ->flushFromModel($workCategoryModel, $workCategory);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.save.success');

                return $this->redirectToRoute('work_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getWorkCategoryForm(ControllerMethodConstant::EDIT_AJAX, $workCategoryModel, $workCategory);
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.work_category_edit');

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/work_category/work_category.html.twig'), [
            'form' => $form->createView(),
            'workCategory' => $workCategory,
            'title' => $this->trans('finalwork.page.work_category_edit'),
            'buttonActionTitle' => $this->trans('finalwork.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('finalwork.form.action.update_and_close')
        ]);
    }

    /**
     * @param WorkCategory $workCategory
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(WorkCategory $workCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        if (count($workCategory->getWorks()) === 0) {
            $this->removeEntity($workCategory);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.delete.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.delete.error');
        }

        return $this->redirectToRoute('work_category_list');
    }

    /**
     * @param string $type
     * @param WorkCategoryModel $workCategoryModel
     * @param WorkCategory|null $workCategory
     * @return FormInterface
     */
    public function getWorkCategoryForm(
        string $type,
        WorkCategoryModel $workCategoryModel,
        WorkCategory $workCategory = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('work_category_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->generateUrl('work_category_edit_ajax', [
                        'id' => $this->hashIdEncode($workCategory->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];
                break;
            default:
                throw new ConstantNotFoundException('Controller method type not found');
        }

        return $this->createForm(WorkCategoryForm::class, $workCategoryModel, $parameters);
    }
}
