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

namespace App\Domain\Work\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Work\Http\{
    WorkListHandle,
    WorkEditHandle,
    WorkDeleteHandle,
    WorkCreateHandle,
    WorkDetailHandle,
    WorkEditAuthorHandle
};
use App\Domain\Work\Entity\Work;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

class WorkController extends AbstractController
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
