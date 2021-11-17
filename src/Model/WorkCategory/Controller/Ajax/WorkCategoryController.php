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

namespace App\Model\WorkCategory\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\WorkCategory;
use App\Model\WorkCategory\Http\Ajax\{
    WorkCategoryEditHandle,
    WorkCategoryCreateHandle,
    WorkCategoryDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkCategoryController extends BaseController
{
    public function __construct(
        private WorkCategoryCreateHandle $workCategoryCreateHandle,
        private WorkCategoryEditHandle $workCategoryEditHandle,
        private WorkCategoryDeleteHandle $workCategoryDeleteHandle
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->workCategoryCreateHandle->handle($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        return $this->workCategoryEditHandle->handle($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        return $this->workCategoryDeleteHandle->handle($workCategory);
    }
}
