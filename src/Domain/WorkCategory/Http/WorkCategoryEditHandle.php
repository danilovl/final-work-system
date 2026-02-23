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

use App\Application\Constant\ControllerMethodConstant;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;
use App\Domain\WorkCategory\Form\Factory\WorkCategoryFormFactory;
use App\Domain\WorkCategory\Model\WorkCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private WorkCategoryFormFactory $workCategoryFormFactory,
        private WorkCategoryFactory $workCategoryFactory
    ) {}

    public function __invoke(Request $request, WorkCategory $workCategory): Response
    {
        $workCategoryModel = WorkCategoryModel::fromMedia($workCategory);

        $form = $this->workCategoryFormFactory->getWorkCategoryForm(
            ControllerMethodConstant::EDIT,
            $workCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workCategoryFactory->flushFromModel($workCategoryModel, $workCategory);

            return $this->requestService->redirectToRoute('work_category_list');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->workCategoryFormFactory->getWorkCategoryForm(
                ControllerMethodConstant::EDIT_AJAX,
                $workCategoryModel,
                $workCategory
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/work_category/work_category.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.work_category_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
