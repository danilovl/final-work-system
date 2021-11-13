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

namespace App\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\MediaCategory;
use App\Model\DocumentCategory\Http\Ajax\{
    DocumentCategoryEditHandle,
    DocumentCategoryCreateHandle,
    DocumentCategoryDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class DocumentCategoryController extends BaseController
{
    public function __construct(
        private DocumentCategoryCreateHandle $documentCategoryCreateHandle,
        private DocumentCategoryEditHandle $documentCategoryEditHandle,
        private DocumentCategoryDeleteHandle $documentCategoryDeleteHandle
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->documentCategoryCreateHandle->handle($request);
    }

    public function edit(Request $request, MediaCategory $mediaCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        return $this->documentCategoryEditHandle->handle($request, $mediaCategory);
    }

    public function delete(MediaCategory $mediaCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        return $this->documentCategoryDeleteHandle->handle($mediaCategory);
    }
}
