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
use App\Entity\WorkCategory;
use App\Model\WorkCategory\Http\{
    WorkCategoryListHandle,
    WorkCategoryEditHandle,
    WorkCategoryCreateHandle,
    WorkCategoryDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkCategoryController extends BaseController
{
    public function __construct(
        private WorkCategoryCreateHandle $workCategoryCreateHandle,
        private WorkCategoryListHandle $workCategoryListHandle,
        private WorkCategoryEditHandle $workCategoryEditHandle,
        private WorkCategoryDeleteHandle $workCategoryDeleteHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->workCategoryCreateHandle->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->workCategoryListHandle->handle($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        return $this->workCategoryEditHandle->handle($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        return $this->workCategoryDeleteHandle->handle($workCategory);
    }
}
