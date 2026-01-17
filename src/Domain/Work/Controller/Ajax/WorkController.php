<?php declare(strict_types=1);

/**
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
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Http\Ajax\{
    WorkEditHandle,
    WorkCreateHandle,
    WorkDeleteHandle,
    WorkEditAuthorHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class WorkController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private WorkCreateHandle $workCreateHandle,
        private WorkEditHandle $workEditHandle,
        private WorkEditAuthorHandle $workEditAuthorHandle,
        private WorkDeleteHandle $workDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->workCreateHandle->__invoke($request);
    }

    public function edit(Request $request, Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->workEditHandle->__invoke($request, $work);
    }

    public function editAuthor(Request $request, Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $work);

        return $this->workEditAuthorHandle->__invoke($request, $work);
    }

    public function delete(Work $work): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $work);

        return $this->workDeleteHandle->__invoke($work);
    }
}
