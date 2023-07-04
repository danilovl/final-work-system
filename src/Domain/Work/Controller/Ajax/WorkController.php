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

namespace App\Domain\Work\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Http\Ajax\{
    WorkEditHandle,
    WorkCreateHandle,
    WorkDeleteHandle,
    WorkEditAuthorHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WorkController extends AbstractController
{
    public function __construct(
        private readonly WorkCreateHandle $workCreateHandle,
        private readonly WorkEditHandle $workEditHandle,
        private readonly WorkEditAuthorHandle $workEditAuthorHandle,
        private readonly WorkDeleteHandle $workDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->workCreateHandle->handle($request);
    }

    public function edit(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->workEditHandle->handle($request, $work);
    }

    public function editAuthor(Request $request, Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->workEditAuthorHandle->handle($request, $work);
    }

    public function delete(Work $work): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $work);

        return $this->workDeleteHandle->handle($work);
    }
}
