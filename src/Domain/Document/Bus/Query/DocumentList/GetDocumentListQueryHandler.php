<?php declare(strict_types=1);

namespace App\Domain\Document\Bus\Query\DocumentList;

use App\Domain\Media\DTO\Repository\MediaRepositoryDTO;
use App\Infrastructure\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\Media\Bus\Query\MediaQueryByUserFilter\{GetMediaQueryByUserFilterQuery, GetMediaQueryByUserFilterQueryResult};

#[AsMessageHandler]
readonly class GetDocumentListQueryHandler
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private EntityManagerService $entityManagerService,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetDocumentListQuery $query): GetDocumentListQueryResult
    {
        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL->value);

        $mediaData = new MediaRepositoryDTO(
            users: $query->users,
            active: $query->active,
            type: $type,
            criteria: $query->criteria
        );

        $getMediaQueryByUserFilterQuery = GetMediaQueryByUserFilterQuery::create($mediaData);
        /** @var GetMediaQueryByUserFilterQueryResult $getMediaQueryByUserFilterQueryResult */
        $getMediaQueryByUserFilterQueryResult = $this->queryBus->handle($getMediaQueryByUserFilterQuery);
        $documents = $getMediaQueryByUserFilterQueryResult->query;
        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $documents,
            detachEntity: $query->detachEntity
        );

        return new GetDocumentListQueryResult($pagination);
    }
}
