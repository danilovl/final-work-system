<?php declare(strict_types=1);

namespace App\Domain\DocumentCategory\Bus\Query\DocumentCategoryList;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\MediaCategory\Bus\Query\MediaCategoryQueryByOwner\{GetMediaCategoryQueryByOwnerQuery, GetMediaCategoryQueryByOwnerQueryResult};

#[AsMessageHandler]
readonly class GetDocumentCategoryListQueryHandler
{
    public function __construct(
        private PaginatorService $paginatorService,
        private QueryBusInterface $queryBus,
        private ParameterServiceInterface $parameterService,
    ) {}

    public function __invoke(GetDocumentCategoryListQuery $query): GetDocumentCategoryListQueryResult
    {
        $getMediaCategoryQueryByOwnerQuery = GetMediaCategoryQueryByOwnerQuery::create($query->user);
        /** @var GetMediaCategoryQueryByOwnerQueryResult $getMediaCategoryQueryByOwnerQueryResult */
        $getMediaCategoryQueryByOwnerQueryResult = $this->queryBus->handle($getMediaCategoryQueryByOwnerQuery);
        $mediaCategoriesQuery = $getMediaCategoryQueryByOwnerQueryResult->query;

        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $mediaCategoriesQuery,
            $this->parameterService->getInt('pagination.default.page'),
            $this->parameterService->getInt('pagination.document_category.limit'),
            detachEntity: true
        );

        return new GetDocumentCategoryListQueryResult($pagination);
    }
}
