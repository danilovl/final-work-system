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
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkCategoryController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.work_category.create')->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.work_category.list')->handle($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $workCategory);

        return $this->get('app.http_handle.work_category.edit')->handle($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $workCategory);

        return $this->get('app.http_handle.work_category.delete')->handle($workCategory);
    }
}
