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

namespace App\Controller;

use App\Constant\VoterSupportConstant;
use App\Entity\MediaCategory;
use App\Model\DocumentCategory\Http\{
    DocumentCategoryListHandle,
    DocumentCategoryEditHandle,
    DocumentCategoryCreateHandle,
    DocumentCategoryDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class DocumentCategoryController extends BaseController
{
    public function __construct(
        private DocumentCategoryCreateHandle $documentCategoryCreateHandle,
        private DocumentCategoryListHandle $documentCategoryListHandle,
        private DocumentCategoryEditHandle $documentCategoryEditHandle,
        private DocumentCategoryDeleteHandle $documentCategoryDeleteHandle,
    ) {
    }

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
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $mediaCategory);

        return $this->documentCategoryEditHandle->handle($request, $mediaCategory);
    }

    public function delete(MediaCategory $mediaCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $mediaCategory);

        return $this->documentCategoryDeleteHandle->handle($mediaCategory);
    }
}
