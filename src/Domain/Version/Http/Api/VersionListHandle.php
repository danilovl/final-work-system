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

namespace App\Domain\Version\Http\Api;

use App\Domain\Media\Factory\MediaDTOFactory;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Media\Bus\Query\MediaQueryByWork\{GetMediaQueryByWorkQuery, GetMediaQueryByWorkQueryResult};
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class VersionListHandle
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private PaginatorService $paginatorService,
        private ParameterServiceInterface $parameterService,
        private MediaDTOFactory $mediaDTOFactory
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $getMediaQueryByWorkQuery = GetMediaQueryByWorkQuery::create($work);
        /** @var GetMediaQueryByWorkQueryResult $getMediaQueryByWorkQueryResult */
        $getMediaQueryByWorkQueryResult = $this->queryBus->handle($getMediaQueryByWorkQuery);
        $query = $getMediaQueryByWorkQueryResult->query;

        $options = [
            PaginatorInterface::PAGE_PARAMETER_NAME => 'page_version',
            PaginatorInterface::SORT_FIELD_PARAMETER_NAME => 'sort_version',
            PaginatorInterface::SORT_DIRECTION_PARAMETER_NAME => 'direction_version',
            PaginatorInterface::FILTER_FIELD_PARAMETER_NAME => 'filterParam_version',
            PaginatorInterface::FILTER_VALUE_PARAMETER_NAME => 'filterValue_version',
            PaginatorInterface::DISTINCT => true
        ];

        $page = $this->parameterService->getInt('pagination.work_detail_tab.version.page');
        $limit = $this->parameterService->getInt('pagination.work_detail_tab.version.limit');

        $paginationVersion = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: $query,
            page: $page,
            limit: $limit,
            options: $options
        );

        $versions = [];
        foreach ($paginationVersion as $version) {
            $versions[] = $this->mediaDTOFactory->create($version);
        }

        return new JsonResponse([
            'count' => $paginationVersion->count(),
            'totalCount' => $paginationVersion->getTotalItemCount(),
            'success' => true,
            'result' => $versions
        ]);
    }
}
