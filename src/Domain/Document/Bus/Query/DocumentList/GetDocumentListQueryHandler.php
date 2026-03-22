<?php declare(strict_types=1);

namespace App\Domain\Document\Bus\Query\DocumentList;

use App\Domain\Media\DTO\Repository\MediaRepositoryDTO;
use App\Infrastructure\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\Media\Facade\MediaFacade;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetDocumentListQueryHandler
{
    public function __construct(
        private MediaFacade $mediaFacade,
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

        $documents = $this->mediaFacade->getMediaListQueryByUserFilter($mediaData);
        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $documents,
            detachEntity: $query->detachEntity
        );

        return new GetDocumentListQueryResult($pagination);
    }
}
