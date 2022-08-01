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
    ControllerMethodConstant
};
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Service\{
    UserService,
    RequestService
};
use App\Domain\Document\Form\Factory\DocumentFormFactory;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaFactory;
use App\Domain\Media\MediaModel;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

class DocumentEditHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly DocumentFormFactory $documentFormFactory,
        private readonly MediaFactory $mediaFactory
    ) {}

    public function handle(Request $request, Media $media): JsonResponse
    {
        $mediaModel = MediaModel::fromMedia($media);
        $form = $this->documentFormFactory
            ->setUser($this->userService->getUser())
            ->getDocumentForm(ControllerMethodConstant::EDIT_AJAX, $mediaModel, $media)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaFactory->flushFromModel($mediaModel, $media);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
