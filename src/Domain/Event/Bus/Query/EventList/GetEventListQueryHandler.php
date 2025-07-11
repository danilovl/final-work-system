<?php declare(strict_types=1);

namespace App\Domain\Event\Bus\Query\EventList;

use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Facade\EventFacade;
use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetEventListQueryHandler
{
    public function __construct(
        private EventFacade $eventFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetEventListQuery $query): GetEventListQueryResult
    {
        $eventRepositoryData = new EventRepositoryDTO(
            user: $query->user
        );

        $eventsQuery = $this->eventFacade->queryEventsByOwner($eventRepositoryData);
        $eventsQuery->setHydrationMode(Event::class);

        $pagination = $this->paginatorService->createPaginationRequest($query->request, $eventsQuery);

        return new GetEventListQueryResult($pagination);
    }
}
