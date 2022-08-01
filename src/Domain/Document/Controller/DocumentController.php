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

namespace App\Domain\Document\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Document\Http\{
    DocumentEditHandle,
    DocumentListHandle,
    DocumentCreateHandle,
    DocumentDownloadHandle,
    DocumentListOwnerHandle,
    DocumentDetailContentHandle
};
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
    public function __construct(
        private readonly DocumentCreateHandle $documentCreateHandle,
        private readonly DocumentDetailContentHandle $documentDetailContentHandle,
        private readonly DocumentEditHandle $documentEditHandle,
        private readonly DocumentListHandle $documentListHandle,
        private readonly DocumentListOwnerHandle $documentListOwnerHandle,
        private readonly DocumentDownloadHandle $documentDownloadHandle
    ) {}

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
