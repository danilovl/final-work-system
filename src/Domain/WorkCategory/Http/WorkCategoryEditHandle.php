<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\WorkCategory\Http;

use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService};
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;
use App\Domain\WorkCategory\Form\Factory\WorkCategoryFormFactory;
use App\Domain\WorkCategory\Model\WorkCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response};

readonly class WorkCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private WorkCategoryFormFactory $workCategoryFormFactory,
        private WorkCategoryFactory $workCategoryFactory
    ) {}

    public function handle(Request $request, WorkCategory $workCategory): Response
    {
        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);

        $form = $this->workCategoryFormFactory->getWorkCategoryForm(
            ControllerMethodConstant::EDIT,
            $workCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->workCategoryFactory
                    ->flushFromModel($workCategoryModel, $workCategory);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('work_category_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->workCategoryFormFactory->getWorkCategoryForm(
                ControllerMethodConstant::EDIT_AJAX,
                $workCategoryModel,
                $workCategory
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'work_category/work_category.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'workCategory' => $workCategory,
            'title' => $this->translatorService->trans('app.page.work_category_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
