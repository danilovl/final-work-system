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

use App\Helper\FormValidationMessageHelper;
use App\Model\Media\Facade\MediaMimeTypeFacade;
use App\Model\Media\Factory\MediaFactory;
use App\Model\Media\MediaModel;
use App\Model\Media\Entity\Media;
use App\Constant\AjaxJsonTypeConstant;
use App\Model\Version\EventDispatcher\VersionEventDispatcherService;
use App\Model\Version\Form\VersionForm;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class VersionEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private MediaMimeTypeFacade $mediaMimeTypeFacade,
        private FormFactoryInterface $formFactory,
        private MediaFactory $mediaFactory,
        private VersionEventDispatcherService $versionEventDispatcherService
    ) {
    }

    public function handle(Request $request, Media $media): JsonResponse
    {
        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->formFactory
            ->create(VersionForm::class, $mediaModel, [
                'mimeTypes' => $this->mediaMimeTypeFacade->getFormValidationMimeTypes(true)
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaFactory->flushFromModel($mediaModel, $media);

            $media->setOwner($this->userService->getUser());
            $this->versionEventDispatcherService->onVersionEdit($media);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
