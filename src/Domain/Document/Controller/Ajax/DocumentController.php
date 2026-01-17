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

namespace App\Domain\Document\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Document\Http\Ajax\{
    DocumentEditHandle,
    DocumentCreateHandle,
    DocumentDeleteHandle,
    DocumentChangeActiveHandle
};
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response
};

readonly class DocumentController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private DocumentCreateHandle $documentCreateHandle,
        private DocumentEditHandle $documentEditHandle,
        private DocumentChangeActiveHandle $documentChangeActiveHandle,
        private DocumentDeleteHandle $documentDeleteHandle,
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->documentCreateHandle->__invoke($request);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $media);

        return $this->documentEditHandle->__invoke($request, $media);
    }

    public function changeActive(Media $media): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $media);

        return $this->documentChangeActiveHandle->__invoke($media);
    }

    public function delete(Media $media): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $media);

        return $this->documentDeleteHandle->__invoke($media);
    }
}
