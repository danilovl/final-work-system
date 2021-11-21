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

namespace App\Model\Work\Controller;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\Work\Entity\Work;
use App\Model\Work\Http\{
    WorkEditHandle,
    WorkListHandle,
    WorkCreateHandle,
    WorkDeleteHandle,
    WorkDetailHandle,
    WorkEditAuthorHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class WorkController extends BaseController
{
    public function __construct(
        private WorkCreateHandle $workCreateHandle,
        private WorkDetailHandle $workDetailHandle,
        private WorkListHandle $workListHandle,
        private WorkEditHandle $workEditHandle,
        private WorkEditAuthorHandle $workEditAuthorHandle,
        private WorkDeleteHandle $workDeleteHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->workCreateHandle->handle($request);
    }

    public function detail(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $work);

        return $this->workDetailHandle->handle($request, $work);
    }

    public function list(Request $request, string $type): Response
    {
        return $this->workListHandle->handle($request, $type);
    }

    public function edit(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->workEditHandle->handle($request, $work);
    }

    public function editAuthor(Request $request, Work $work): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $work);

        return $this->workEditAuthorHandle->handle($request, $work);
    }

    public function delete(Request $request, Work $work): RedirectResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $work);

        return $this->workDeleteHandle->handle($request, $work);
    }
}
