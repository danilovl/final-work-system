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

namespace App\Domain\Document\Http;

use App\Application\Constant\{
    MediaTypeConstant,
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Application\Service\{
    UserService,
    RequestService,
    TranslatorService,
    EntityManagerService,
    TwigRenderService
};
use App\Domain\Document\EventDispatcher\DocumentEventDispatcherService;
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\MediaModel;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private MediaFactory $mediaFactory,
        private DocumentEventDispatcherService $documentEventDispatcherService
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(
            MediaType::class,
            MediaTypeConstant::INFORMATION_MATERIAL
        );

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->type = $type;

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::CREATE, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $media = $this->mediaFactory->flushFromModel($mediaModel);
                $this->documentEventDispatcherService->onDocumentCreate($media);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('document_list_owner');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentFormFactory
                ->setUser($user)
                ->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel)
                ->handleRequest($request);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'document/document.html.twig');

        return $this->twigRenderService->render($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.information_material_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
