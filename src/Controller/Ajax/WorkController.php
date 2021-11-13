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
use App\Model\Work\Http\Ajax\{
    WorkEditHandle,
    WorkCreateHandle,
    WorkDeleteHandle,
    WorkEditAuthorHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkController extends BaseController
{
    public function __construct(
        private WorkCreateHandle $workCreateHandle,
        private WorkEditHandle $workEditHandle,
        private WorkEditAuthorHandle $workEditAuthorHandle,
        private WorkDeleteHandle $workDeleteHandle
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->workCreateHandle->handle($request);
    }

    public function edit(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->workEditHandle->handle($request, $work);
    }

    public function editAuthor(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->workEditAuthorHandle->handle($request, $work);
    }

    public function delete(Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        return $this->workDeleteHandle->handle($work);
    }
}
