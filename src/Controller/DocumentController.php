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
use App\Entity\Media;
use App\Model\Document\Http\{
    DocumentEditHandle,
    DocumentListHandle,
    DocumentCreateHandle,
    DocumentDownloadHandle,
    DocumentListOwnerHandle,
    DocumentDetailContentHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentController extends BaseController
{
    public function __construct(
        private DocumentCreateHandle $documentCreateHandle,
        private DocumentDetailContentHandle $documentDetailContentHandle,
        private DocumentEditHandle $documentEditHandle,
        private DocumentListHandle $documentListHandle,
        private DocumentListOwnerHandle $documentListOwnerHandle,
        private DocumentDownloadHandle $documentDownloadHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->documentCreateHandle->handle($request);
    }

    public function detailContent(Media $media): Response
    {
        return $this->documentDetailContentHandle->handle($media);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->documentEditHandle->handle($request, $media);
    }

    public function list(Request $request): Response
    {
        return $this->documentListHandle->handle($request);
    }

    public function listOwner(Request $request): Response
    {
        return $this->documentListOwnerHandle->handle($request);
    }

    public function download(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->documentDownloadHandle->handle($media);

        return new Response;
    }

    public function downloadGoogle(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->documentDownloadHandle->handle($media);

        return new Response;
    }
}
