<?php declare(strict_types=1);

namespace App\Domain\Document\Bus\Query\DocumentList;

use App\Application\Service\{
    PaginatorService,
    EntityManagerService
};
use App\Domain\Media\DataTransferObject\MediaRepositoryData;
use App\Domain\Media\Facade\MediaFacade;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetDocumentListQueryHandler
{
    public function __construct(
        private UserFacade $userFacade,
        private MediaFacade $mediaFacade,
        private EntityManagerService $entityManagerService,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetDocumentListQuery $query): GetDocumentListQueryResult
    {
        /** @var MediaType $type */
        $type = $this->entityManagerService->getReference(MediaType::class, MediaTypeConstant::INFORMATION_MATERIAL->value);

        $mediaData = MediaRepositoryData::createFromArray([
            'users' => $this->userFacade->getAllUserActiveSupervisors($query->user),
            'type' => $type,
            'active' => true,
            'criteria' => $query->criteria
        ]);

        $documents = $this->mediaFacade->getMediaListQueryByUserFilter($mediaData);
        $pagination = $this->paginatorService->createPaginationRequest($query->request, $documents, detachEntity: true);

        return new GetDocumentListQueryResult($pagination);
    }
}
