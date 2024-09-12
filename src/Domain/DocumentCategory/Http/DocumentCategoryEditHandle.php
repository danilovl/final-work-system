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

namespace App\Domain\DocumentCategory\Http;

use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\DocumentCategory\Form\Factory\DocumentCategoryFormFactory;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Factory\MediaCategoryFactory;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class DocumentCategoryEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private MediaCategoryFactory $mediaCategoryFactory,
        private DocumentCategoryFormFactory $documentCategoryFormFactory,
    ) {}

    public function __invoke(Request $request, MediaCategory $mediaCategory): Response
    {
        $mediaCategoryModel = MediaCategoryModel::fromMediaCategory($mediaCategory);

        $form = $this->documentCategoryFormFactory->getDocumentCategoryForm(
            ControllerMethodConstant::EDIT,
            $mediaCategoryModel
        );
        $form = $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->mediaCategoryFactory
                    ->flushFromModel($mediaCategoryModel, $mediaCategory);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('document_category_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentCategoryFormFactory->getDocumentCategoryForm(
                ControllerMethodConstant::EDIT_AJAX,
                $mediaCategoryModel,
                $mediaCategory
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/document_category/document_category.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.information_materials_category_edit'),
            'mediaCategory' => $mediaCategory,
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
