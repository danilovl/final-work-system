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

use App\Domain\User\Service\UserService;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkFacade $workFacade,
        private PaginatorService $paginatorService,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $user = $this->userService->getUser();

        $workRepositoryDTO = new WorkRepositoryDTO(
            user: $user,
            type: $type,
            workStatus: [WorkStatusConstant::ACTIVE->value]
        );

        $worksQuery = $this->workFacade->queryByUserStatus($workRepositoryDTO);
        $pagination = $this->paginatorService->createPaginationRequest($request, $worksQuery);

        $works = [];
        foreach ($pagination as $work) {
            $works[] = $this->objectToArrayTransformService->transform('api_key_field', $work);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $works
        ]);
    }
}
