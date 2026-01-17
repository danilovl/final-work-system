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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\DocumentCategory\Bus\Command\CreateDocumentCategory\CreateDocumentCategoryCommand;
use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant
};
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\DocumentCategory\Form\Factory\DocumentCategoryFormFactory;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class DocumentCategoryCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TranslatorService $translatorService,
        private TwigRenderService $twigRenderService,
        private DocumentCategoryFormFactory $documentCategoryFormFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
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
                $command = CreateDocumentCategoryCommand::create($mediaCategoryModel);
                $this->commandBus->dispatch($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('document_category_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentCategoryFormFactory->getDocumentCategoryForm(
                ControllerMethodConstant::CREATE_AJAX,
                $mediaCategoryModel
            );
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/document_category/document_category.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.information_materials_category_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
