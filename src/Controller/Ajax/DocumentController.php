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
use App\Entity\Media;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse
};

class DocumentController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.document.create')->handle($request);
    }

    public function edit(Request $request, Media $media): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->get('app.http_handle_ajax.document.edit')->handle($request, $media);
    }

    public function changeActive(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $media);

        return $this->get('app.http_handle_ajax.document.change_active')->handle($media);
    }

    public function delete(Media $media): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $media);

        return $this->get('app.http_handle_ajax.document.delete')->handle($media);
    }
}
