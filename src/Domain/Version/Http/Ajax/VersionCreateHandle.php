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

namespace App\Domain\Version\Http\Ajax;

use App\Application\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant
};
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use App\Domain\Media\Facade\MediaMimeTypeFacade;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\MediaModel;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;
use App\Domain\Version\Form\VersionForm;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class VersionCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private MediaMimeTypeFacade $mediaMimeTypeFacade,
        private EntityManagerService $entityManagerService,
        private FormFactoryInterface $formFactory,
        private MediaFactory $mediaFactory,
        private VersionEventDispatcherService $versionEventDispatcherService
    ) {
    }

    public function handle(Request $request, Work $work): JsonResponse
    {
        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION);

        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $type;

        $form = $this->formFactory
            ->create(VersionForm::class, $mediaModel, [
                'uploadMedia' => true,
                'mimeTypes' => $this->mediaMimeTypeFacade->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->mediaFactory->flushFromModel($mediaModel);
            $this->versionEventDispatcherService->onVersionCreate($media);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
