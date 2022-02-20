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

namespace App\Domain\Work\Http\Api;

use App\Application\Constant\WorkStatusConstant;
use App\Application\DataTransferObject\Repository\WorkData;
use App\Application\Service\{
    UserService,
    PaginatorService
};
use App\Domain\Work\Facade\WorkFacade;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class WorkListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkFacade $workFacade,
        private PaginatorService $paginatorService,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {
    }

    public function handle(Request $request, string $type): JsonResponse
    {
        $user = $this->userService->getUser();

        $workData = WorkData::createFromArray([
            'user' => $user,
            'type' => $type,
            'workStatus' => [WorkStatusConstant::ACTIVE]
        ]);

        $worksQuery = $this->workFacade->queryAllByUserStatus($workData);
        $pagination = $this->paginatorService->createPaginationRequest($request, $worksQuery);

        $works = [];
        foreach ($pagination as $work) {
            $works[] = $this->objectToArrayTransformService->transform('api_key_field', $work);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'works' => $works
        ]);
    }
}