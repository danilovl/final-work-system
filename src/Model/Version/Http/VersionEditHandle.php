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

namespace App\Model\Version\Http;

use App\Entity\Work;
use App\Model\Media\Factory\MediaFactory;
use App\Model\Media\MediaModel;
use App\Entity\Media;
use App\Model\Version\EventDispatcher\VersionEventDispatcherService;
use App\Model\Version\Form\Factory\VersionFormFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Constant\{
    SeoPageConstant,
    FlashTypeConstant,
    ControllerMethodConstant
};
use App\Service\{
    UserService,
    SeoPageService,
    RequestService,
    TranslatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class VersionEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private VersionFormFactory $versionFormFactory,
        private HashidsServiceInterface $hashidsService,
        private TwigRenderService $twigRenderService,
        private TranslatorService $translatorService,
        private SeoPageService $seoPageService,
        private MediaFactory $mediaFactory,
        private VersionEventDispatcherService $versionEventDispatcherService
    ) {
    }

    public function handle(
        Request $request,
        Work $work,
        Media $media
    ): Response {
        $user = $this->userService->getUser();
        $mediaModel = MediaModel::fromMedia($media);

        $form = $this->versionFormFactory
            ->getVersionForm(
                ControllerMethodConstant::EDIT,
                $mediaModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->mediaFactory->flushFromModel($mediaModel, $media);

                $media->setOwner($user);
                $this->versionEventDispatcherService->onVersionEdit($media);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->versionFormFactory->getVersionForm(
                ControllerMethodConstant::EDIT_AJAX,
                $mediaModel,
                $media,
                $work
            );
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'version/version.html.twig');

        return $this->twigRenderService->render($template, [
            'work' => $work,
            'media' => $media,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.version_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
