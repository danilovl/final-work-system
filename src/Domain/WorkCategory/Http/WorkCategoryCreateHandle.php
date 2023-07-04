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
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Application\Service\{
    UserService,
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;
use App\Domain\WorkCategory\Form\Factory\WorkCategoryFormFactory;
use App\Domain\WorkCategory\WorkCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkCategoryCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private WorkCategoryFormFactory $categoryFormFactory,
        private WorkCategoryFactory $workCategoryFactory
    ) {}

    public function handle(Request $request): Response
    {
        $workCategoryModel = new WorkCategoryModel;
        $workCategoryModel->owner = $this->userService->getUser();

        $form = $this->categoryFormFactory->getWorkCategoryForm(
            ControllerMethodConstant::CREATE,
            $workCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->workCategoryFactory->flushFromModel($workCategoryModel);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('work_category_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->categoryFormFactory->getWorkCategoryForm(
                ControllerMethodConstant::CREATE_AJAX,
                $workCategoryModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'work_category/work_category.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.work_category_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
