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

namespace App\Domain\DocumentCategory\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\DocumentCategory\Http\{
    DocumentCategoryEditHandle,
    DocumentCategoryListHandle,
    DocumentCategoryCreateHandle,
    DocumentCategoryDeleteHandle
};
use App\Domain\MediaCategory\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

readonly class DocumentCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private DocumentCategoryCreateHandle $documentCategoryCreateHandle,
        private DocumentCategoryListHandle $documentCategoryListHandle,
        private DocumentCategoryEditHandle $documentCategoryEditHandle,
        private DocumentCategoryDeleteHandle $documentCategoryDeleteHandle,
    ) {}

    public function create(Request $request): Response
    {
        return $this->documentCategoryCreateHandle->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->documentCategoryListHandle->handle($request);
    }

    public function edit(Request $request, MediaCategory $mediaCategory): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $mediaCategory);

        return $this->documentCategoryEditHandle->handle($request, $mediaCategory);
    }

    public function delete(MediaCategory $mediaCategory): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $mediaCategory);

        return $this->documentCategoryDeleteHandle->handle($mediaCategory);
    }
}
