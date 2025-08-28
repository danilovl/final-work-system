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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\Bus\Command\CreateVersion\CreateVersionCommand;
use App\Application\Constant\{
    ControllerMethodConstant,
    FlashTypeConstant,
    SeoPageConstant
};
use App\Application\Service\{
    EntityManagerService,
    RequestService,
    SeoPageService,
    TranslatorService,
    TwigRenderService
};
use App\Domain\Media\Model\MediaModel;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Service\UserService;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Domain\Version\Form\Factory\VersionFormFactory;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class VersionCreateHandle
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
        private VersionEventDispatcherService $versionEventDispatcherService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Work $work): Response
    {
        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION->value);

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $type;

        $form = $this->versionFormFactory
            ->getVersionForm(
                ControllerMethodConstant::CREATE,
                $mediaModel
            )
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $createVersionCommand = CreateVersionCommand::create($mediaModel);
                /** @var Media $media */
                $media = $this->commandBus->dispatchResult($createVersionCommand);

                $this->versionEventDispatcherService->onVersionCreate($media);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('work_detail', [
                    'id' => $this->hashidsService->encode($work->getId())
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        if ($request->isXmlHttpRequest()) {
            $form = $this->versionFormFactory->getVersionForm(
                ControllerMethodConstant::CREATE_AJAX,
                $mediaModel,
                null,
                $work
            );
        }

        $this->seoPageService->addTitle($work->getTitle(), SeoPageConstant::DASH_SEPARATOR->value);

        $template = $this->twigRenderService->ajaxOrNormalFolder($request, 'domain/version/version.html.twig');

        return $this->twigRenderService->renderToResponse($template, [
            'work' => $work,
            'form' => $form->createView(),
            'title' => $this->translatorService->trans('app.page.version_add'),
            'buttonActionTitle' => $this->translatorService->trans('app.form.action.create'),
            'buttonActionCloseTitle' => $this->translatorService->trans('app.form.action.create_and_close')
        ]);
    }
}
