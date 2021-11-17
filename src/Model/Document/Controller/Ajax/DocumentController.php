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

namespace App\Model\Document\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\Media;
use App\Model\Document\Http\Ajax\{
    DocumentEditHandle,
    DocumentCreateHandle,
    DocumentDeleteHandle,
    DocumentChangeActiveHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

class DocumentController extends BaseController
{
    public function __construct(
        private DocumentCreateHandle $documentCreateHandle,
        private DocumentEditHandle $documentEditHandle,
        private DocumentChangeActiveHandle $documentChangeActiveHandle,
        private DocumentDeleteHandle $documentDeleteHandle,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->documentCreateHandle->handle($request);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->documentEditHandle->handle($request, $media);
    }

    public function changeActive(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->documentChangeActiveHandle->handle($media);
    }

    public function delete(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $media);

        return $this->documentDeleteHandle->handle($media);
    }
}
