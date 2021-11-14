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

namespace App\Model\Document\Http\Ajax;

use App\Entity\MediaType;
use App\Model\Document\EventDispatcher\DocumentEventDispatcherService;
use App\Model\Document\Form\Factory\DocumentFormFactory;
use App\Helper\FormValidationMessageHelper;
use App\Model\Media\Factory\MediaFactory;
use App\Model\Media\MediaModel;
use App\Constant\{
    MediaTypeConstant,
    AjaxJsonTypeConstant,
    ControllerMethodConstant
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

class DocumentCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private MediaFactory $mediaFactory,
        private DocumentEventDispatcherService $documentEventDispatcherService
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $mediaModel = new MediaModel;
        $mediaModel->owner = $user;
        $mediaModel->type = $this->entityManagerService->getReference(
            MediaType::class,
            MediaTypeConstant::INFORMATION_MATERIAL
        );

        $form = $this->documentFormFactory
            ->setUser($user)
            ->getDocumentForm(ControllerMethodConstant::CREATE_AJAX, $mediaModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $this->mediaFactory->flushFromModel($mediaModel);

            $this->documentEventDispatcherService->onDocumentCreate($media);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
