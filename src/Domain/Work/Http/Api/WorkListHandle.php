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

use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Bus\Query\WorkList\{
    GetWorkListQuery,
    GetWorkListQueryResult
};
use App\Domain\Work\DTO\Api\Input\WorkSearchInput;
use App\Domain\Work\Factory\WorkDetailDTOFactory;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private WorkDetailDTOFactory $workDetailDTOFactory
    ) {}

    public function __invoke(
        Request $request,
        string $type,
        ?WorkSearchInput $workSearchInput = null
    ): JsonResponse {
        $user = $this->userService->getUser();

        if ($workSearchInput?->page !== null) {
            $request->query->set('page', (string) $workSearchInput->page);
        }
        if ($workSearchInput?->limit !== null) {
            $request->query->set('limit', (string) $workSearchInput->limit);
        }

        $searchParams = [];
        if ($workSearchInput !== null) {
            $searchParams = $workSearchInput->toArray();
            unset($searchParams[$type]);
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
            $works[] = $this->workDetailDTOFactory->create($work);
        }

        return new JsonResponse([
            'count' => $result->works->count(),
            'totalCount' => $result->works->getTotalItemCount(),
            'success' => true,
            'result' => $works
        ]);
    }
}
