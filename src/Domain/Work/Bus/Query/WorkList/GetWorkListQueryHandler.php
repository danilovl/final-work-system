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

namespace App\Domain\Work\Bus\Query\WorkList;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Helper\WorkFunctionHelper;
use App\Domain\Work\Repository\Elastica\ElasticaWorkRepository;

readonly class GetWorkListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private PaginatorService $paginatorService,
        private ElasticaWorkRepository $elasticaWorkRepository
    ) {}

    public function __invoke(GetWorkListQuery $query): GetWorkListQueryResult
    {
        $works = $this->elasticaWorkRepository->filterWorkList(
            $query->user,
            $query->type,
            $query->searchData
        );

        $workGroups = match ($query->type) {
            WorkUserTypeConstant::SUPERVISOR->value => WorkFunctionHelper::groupWorksByCategoryAndSorting($works),
            default => WorkFunctionHelper::groupWorksByDeadline($works)
        };

        $pagination = $this->paginatorService->createPaginationRequest($query->request, $workGroups);

        return new GetWorkListQueryResult(workGroups: $pagination);
    }
}
