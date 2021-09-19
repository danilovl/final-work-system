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
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class DocumentController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.document.create')->handle($request);
    }

    public function detailContent(Media $media): Response
    {
        return $this->get('app.http_handle.document.detail_content')->handle($media);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->get('app.http_handle.document.edit')->handle($request, $media);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.document.list')->handle($request);
    }

    public function listOwner(Request $request): Response
    {
        return $this->get('app.http_handle.document.list_owner')->handle($request);
    }

    public function download(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->get('app.http_handle.document.download')->handle($media);

        return new Response;
    }

    public function downloadGoogle(Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DOWNLOAD, $media);

        $this->get('app.http_handle.document.download')->handle($media);

        return new Response;
    }
}
