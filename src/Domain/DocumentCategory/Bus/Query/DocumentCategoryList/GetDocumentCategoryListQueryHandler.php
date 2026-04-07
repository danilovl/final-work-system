<?php declare(strict_types=1);

namespace App\Domain\DocumentCategory\Bus\Query\DocumentCategoryList;

use App\Domain\MediaCategory\Facade\MediaCategoryFacade;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetDocumentCategoryListQueryHandler
{
    public function __construct(
        private PaginatorService $paginatorService,
        private MediaCategoryFacade $mediaCategoryFacade,
        private ParameterServiceInterface $parameterService,
    ) {}

    public function __invoke(GetDocumentCategoryListQuery $query): GetDocumentCategoryListQueryResult
    {
        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $this->mediaCategoryFacade->queryByOwner($query->user),
            $this->parameterService->getInt('pagination.default.page'),
            $this->parameterService->getInt('pagination.document_category.limit'),
            detachEntity: true
        );

        return new GetDocumentCategoryListQueryResult($pagination);
    }
}
