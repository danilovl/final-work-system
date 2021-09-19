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

use App\Entity\Media;
use App\Form\Factory\DocumentFormFactory;
use App\Helper\FormValidationMessageHelper;
use App\Model\Media\Factory\MediaFactory;
use App\Model\Media\MediaModel;
use App\Constant\{
    AjaxJsonTypeConstant,
    ControllerMethodConstant
};
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class DocumentEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private DocumentFormFactory $documentFormFactory,
        private MediaFactory $mediaFactory
    ) {
    }

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
