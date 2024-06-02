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
    ControllerMethodConstant,
    FlashTypeConstant};
use App\Application\Service\{
    RequestService,
    TranslatorService,
    TwigRenderService};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\Model\MediaModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response};

readonly class DocumentEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private DocumentFormFactory $documentFormFactory,
        private MediaFactory $mediaFactory
    ) {}

    public function handle(Request $request, Media $media): Response
    {
        $user = $this->userService->getUser();
        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::EDIT, $mediaModel, $media)
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->mediaFactory->flushFromModel($mediaModel, $media);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('document_edit', ['id' => $media->getId()]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->documentFormFactory
                ->setUser($user)
                ->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media)
                ->handleRequest($request);
        }

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/document/document.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'media' => $media,
            'title' => $this->translatorService->trans('app.page.information_material_edit'),
            'form' => $form->createView(),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
