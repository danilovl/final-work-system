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

namespace App\Domain\Version\Http;

use App\Application\Constant\{
    FlashTypeConstant,
    ControllerMethodConstant,
    MediaTypeConstant,
    SeoPageConstant
};
use App\Application\Service\{
    UserService,
    SeoPageService,
    RequestService,
    TranslatorService,
    TwigRenderService,
    EntityManagerService
};
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\MediaModel;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Domain\Version\Form\Factory\VersionFormFactory;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class VersionCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private VersionFormFactory $versionFormFactory,
        private HashidsServiceInterface $hashidsService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private SeoPageService $seoPageService,
        private EntityManagerService $entityManagerService,
        private MediaFactory $mediaFactory,
        private VersionEventDispatcherService $versionEventDispatcherService
    ) {
    }

    public function handle(Request $request, Work $work): Response
    {
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION);

        $form = $this->versionFormFactory
            ->getVersionForm(
                ControllerMethodConstant::CREATE,
                $mediaModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $media = $this->mediaFactory->flushFromModel($mediaModel);
                $this->versionEventDispatcherService->onVersionCreate($media);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->versionFormFactory->getVersionForm(
                ControllerMethodConstant::CREATE_AJAX,
                $mediaModel,
                null,
                $work
            );
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'version/version.html.twig');

        return $this->twigRenderService->render($template, [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.version_add'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
