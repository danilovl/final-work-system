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
use App\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.work.create')->handle($request);
    }

    public function edit(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->get('app.http_handle_ajax.work.edit')->handle($request, $work);
    }

    public function editAuthor(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->get('app.http_handle_ajax.work.edit_author')->handle($request, $work);
    }

    public function delete(Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        return $this->get('app.http_handle_ajax.work.delete')->handle($work);
    }
}
