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

namespace App\Domain\WorkCategory\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Http\Ajax\{
    WorkCategoryEditHandle,
    WorkCategoryCreateHandle,
    WorkCategoryDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class WorkCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private WorkCategoryCreateHandle $workCategoryCreateHandle,
        private WorkCategoryEditHandle $workCategoryEditHandle,
        private WorkCategoryDeleteHandle $workCategoryDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->workCategoryCreateHandle->handle($request);
    }

    public function edit(Request $request, WorkCategory $workCategory): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $workCategory);

        return $this->workCategoryEditHandle->handle($request, $workCategory);
    }

    public function delete(WorkCategory $workCategory): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::DELETE->value, $workCategory);

        return $this->workCategoryDeleteHandle->handle($workCategory);
    }
}
