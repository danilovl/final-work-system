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

namespace App\Domain\Document\Http\Ajax;

use App\Application\Constant\{
    AjaxJsonTypeConstant,
    ControllerMethodConstant};
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\{
    UserService};
use App\Application\Service\EntityManagerService;
use App\Application\Service\RequestService;
use App\Domain\Document\EventDispatcher\DocumentEventDispatcherService;
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\MediaModel;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request};

readonly class DocumentCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private DocumentFormFactory $documentFormFactory,
        private MediaFactory $mediaFactory,
        private DocumentEventDispatcherService $documentEventDispatcherService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();
        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(
            MediaType::class,
            MediaTypeConstant::INFORMATION_MATERIAL->value
        );

        $mediaModel = new MediaModel;
        $mediaModel->owner = $user;
        $mediaModel->type = $type;

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
