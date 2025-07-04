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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Document\Bus\Command\CreateDocument\CreateDocumentCommand;
use App\Application\Constant\ControllerMethodConstant;
use App\Infrastructure\Service\{
    EntityManagerService,
    RequestService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class DocumentCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(
            MediaType::class,
            MediaTypeConstant::INFORMATION_MATERIAL->value
        );

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->type = $type;

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::CREATE, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateDocumentCommand::create($mediaModel);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('document_list_owner');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentFormFactory
                ->setUser($user)
                ->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/document/document.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.information_material_create'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
