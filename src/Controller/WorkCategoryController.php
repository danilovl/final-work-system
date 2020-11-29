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

use App\Exception\ConstantNotFoundException;
use App\Model\WorkCategory\WorkCategoryModel;
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ControllerMethodConstant
};
use App\Entity\WorkCategory;
use App\Form\WorkCategoryForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkCategoryController extends BaseController
{
    public function create(Request $request): Response
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->getUser();

        $form = $this->getWorkCategoryForm(ControllerMethodConstant::CREATE, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.work_category')
                    ->flushFromModel($workCategoryModel);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('work_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getWorkCategoryForm(ControllerMethodConstant::CREATE_AJAX, $workCategoryModel);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'work_category/work_category.html.twig'), [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.work_category_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }

    public function list(Request $request): Response
    {
        return $this->render('work_category/list.html.twig', [
            'workCategories' => $this->createPagination(
                $request,
                $this->get('app.facade.work.category')->queryWorkCategoriesByOwner($this->getUser())
            )
        ]);
    }

    public function edit(
        Request $request,
        WorkCategory $workCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);
        $form = $this->getWorkCategoryForm(ControllerMethodConstant::EDIT, $workCategoryModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('app.factory.work_category')
                    ->flushFromModel($workCategoryModel, $workCategory);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->redirectToRoute('work_category_list');
            }

            $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.save.warning');
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->getWorkCategoryForm(ControllerMethodConstant::EDIT_AJAX, $workCategoryModel, $workCategory);
        }

        return $this->render($this->ajaxOrNormalFolder($request, 'work_category/work_category.html.twig'), [
            'form' => $form->createView(),
            'workCategory' => $workCategory,
            'title' => $this->trans('app.page.work_category_edit'),
            'buttonActionTitle' => $this->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.update_and_close')
        ]);
    }

    public function delete(WorkCategory $workCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        if (count($workCategory->getWorks()) === 0) {
            $this->removeEntity($workCategory);

            $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');
        } else {
            $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->redirectToRoute('work_category_list');
    }

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
