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

namespace App\Domain\WorkCategory\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Http\{
    WorkCategoryEditHandle,
    WorkCategoryListHandle,
    WorkCategoryDeleteHandle,
    WorkCategoryCreateHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

readonly class WorkCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private WorkCategoryCreateHandle $workCategoryCreateHandle,
        private WorkCategoryListHandle $workCategoryListHandle,
        private WorkCategoryEditHandle $workCategoryEditHandle,
        private WorkCategoryDeleteHandle $workCategoryDeleteHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->workCategoryCreateHandle->__invoke($request);
    }

    public function list(Request $request): Response
    {
        return $this->workCategoryListHandle->__invoke($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $workCategory);

        return $this->workCategoryEditHandle->__invoke($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): RedirectResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $workCategory);

        return $this->workCategoryDeleteHandle->__invoke($workCategory);
    }
}
