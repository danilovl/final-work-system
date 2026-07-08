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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Bus\Query\WorkList\{
    GetWorkListQuery,
    GetWorkListQueryResult
};
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function __invoke(Request $request, string $type, ?string $search = null): JsonResponse
    {
        $user = $this->userService->getUser();

        $searchParams = [];
        if ($search !== null) {
            $searchParams = ['title' => $search];
        }

        $query = GetWorkListQuery::create(
            request: $request,
            user: $user,
            type: $type,
            search: $searchParams
        );

        /** @var GetWorkListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $works = [];
        foreach ($result->works as $work) {
            $works[] = $this->objectToArrayTransformService->transform('api_key_field', $work);
        }

        return new JsonResponse([
            'count' => $result->works->count(),
            'totalCount' => $result->works->getTotalItemCount(),
            'success' => true,
            'result' => $works
        ]);
    }
}
