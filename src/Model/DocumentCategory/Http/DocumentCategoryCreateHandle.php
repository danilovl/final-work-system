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

namespace App\Model\DocumentCategory\Http;

use App\Form\Factory\DocumentCategoryFormFactory;
use App\Model\MediaCategory\Factory\MediaCategoryFactory;
use App\Model\MediaCategory\MediaCategoryModel;
use App\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Service\{
    UserService,
    RequestService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentCategoryCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private DocumentCategoryFormFactory $documentCategoryFormFactory,
        private MediaCategoryFactory $mediaCategoryFactory
    ) {
    }

    public function handle(Request $request): Response
    {
        $mediaCategoryModel = new MediaCategoryModel;
        $mediaCategoryModel->owner = $this->userService->getUser();

        $form = $this->documentCategoryFormFactory->getDocumentCategoryForm(
            ControllerMethodConstant::CREATE,
            $mediaCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->mediaCategoryFactory
                    ->flushFromModel($mediaCategoryModel);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('document_category_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentCategoryFormFactory->getDocumentCategoryForm(
                ControllerMethodConstant::CREATE_AJAX,
                $mediaCategoryModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'document_category/document_category.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'title' => $this->trans('app.page.information_materials_category_create'),
            'buttonActionTitle' => $this->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->trans('app.form.action.create_and_close')
        ]);
    }
}
