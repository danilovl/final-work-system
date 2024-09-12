<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\DocumentCategory\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\DocumentCategory\Http\Ajax\{
    DocumentCategoryEditHandle,
    DocumentCategoryCreateHandle,
    DocumentCategoryDeleteHandle
};
use App\Domain\MediaCategory\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class DocumentCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private DocumentCategoryCreateHandle $documentCategoryCreateHandle,
        private DocumentCategoryEditHandle $documentCategoryEditHandle,
        private DocumentCategoryDeleteHandle $documentCategoryDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->documentCategoryCreateHandle->__invoke($request);
    }

    public function edit(Request $request, MediaCategory $mediaCategory): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $mediaCategory);

        return $this->documentCategoryEditHandle->__invoke($request, $mediaCategory);
    }

    public function delete(MediaCategory $mediaCategory): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $mediaCategory);

        return $this->documentCategoryDeleteHandle->__invoke($mediaCategory);
    }
}
