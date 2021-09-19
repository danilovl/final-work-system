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
use App\Entity\WorkCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkCategoryController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.work_category.create')->handle($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        return $this->get('app.http_handle_ajax.work_category.edit')->handle($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        return $this->get('app.http_handle_ajax.work_category.delete')->handle($workCategory);
    }
}
