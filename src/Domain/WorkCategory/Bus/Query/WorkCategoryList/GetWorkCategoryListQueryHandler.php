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

namespace App\Domain\WorkCategory\Bus\Query\WorkCategoryList;

use App\Infrastructure\Service\PaginatorService;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetWorkCategoryListQueryHandler
{
    public function __construct(
        private WorkCategoryFacade $workCategoryFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetWorkCategoryListQuery $query): GetWorkCategoryListQueryResult
    {
        $workCategoriesByOwnerQuery = $this->workCategoryFacade->queryWorkCategoriesByOwner($query->user);

        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $workCategoriesByOwnerQuery,
            detachEntity: true
        );

        return new GetWorkCategoryListQueryResult($pagination);
    }
}
