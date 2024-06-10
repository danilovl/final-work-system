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
    ControllerMethodConstant,
    FlashTypeConstant,
    SeoPageConstant
};
use App\Application\Service\{
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\Model\MediaModel;
use App\Domain\User\Service\UserService;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Domain\Version\Form\Factory\VersionFormFactory;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class VersionEditHandle
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
    ) {}

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

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.save.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->versionFormFactory->getVersionForm(
                ControllerMethodConstant::EDIT_AJAX,
                $mediaModel,
                $media,
                $work
            );
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR->value);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/version/version.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'work' => $work,
            'media' => $media,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.version_edit'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.update'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.update_and_close')
        ]);
    }
}
