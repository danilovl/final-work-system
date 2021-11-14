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

namespace App\Model\Version\Http\Ajax;

use App\Entity\MediaType;
use App\Entity\Work;
use App\Helper\FormValidationMessageHelper;
use App\Model\Media\Facade\MediaMimeTypeFacade;
use App\Model\Media\Factory\MediaFactory;
use App\Model\Media\MediaModel;
use App\Model\Version\EventDispatcher\VersionEventDispatcherService;
use App\Model\Version\Form\VersionForm;
use App\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant
};
use App\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Form\FormFactoryInterface;

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
        $mediaModel = new MediaModel;
        $mediaModel->owner = $this->userService->getUser();
        $mediaModel->work = $work;
        $mediaModel->type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::WORK_VERSION);

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
