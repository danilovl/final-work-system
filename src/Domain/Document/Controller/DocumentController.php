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
use App\Application\Service\AuthorizationCheckerService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
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
    Response,
    BinaryFileResponse
};

readonly class DocumentController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private DocumentCreateHandle $documentCreateHandle,
        private DocumentDetailContentHandle $documentDetailContentHandle,
        private DocumentEditHandle $documentEditHandle,
        private DocumentListHandle $documentListHandle,
        private DocumentListOwnerHandle $documentListOwnerHandle,
        private DocumentDownloadHandle $documentDownloadHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->documentCreateHandle->handle($request);
    }

    #[AsTwigFunction('document_detail_content')]
    public function detailContent(Media $media): Response
    {
        return $this->documentDetailContentHandle->handle($media);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $media);

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

    public function download(Media $media): BinaryFileResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD->value, $media);

        return $this->documentDownloadHandle->handle($media);
    }

    public function downloadGoogle(Media $media): BinaryFileResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD->value, $media);

        return $this->documentDownloadHandle->handle($media);
    }
}
