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

namespace App\Domain\Event\Http\Api;

use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\Event\DTO\Api\EventDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\Work\Entity\Work;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use App\Domain\Event\Facade\EventFacade;
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class EventListHandle
{
    public function __construct(
        private EventFacade $eventFacade,
        private PaginatorService $paginatorService,
        private ParameterServiceInterface $parameterService,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $query = $this->eventFacade->queryByWork($work);
        $page = $this->parameterService->getInt('pagination.work_detail_tab.event.page');
        $limit = $this->parameterService->getInt('pagination.work_detail_tab.event.limit');

        $pagination = $this->paginatorService->createPaginationRequest(
            request: $request,
            target: $query,
            page: $page,
            limit: $limit,
            options: [PaginatorInterface::DISTINCT => true]
        );

        $events = [];
        /** @var Event $event */
        foreach ($pagination as $event) {
            $events[] = $this->objectToDtoMapper->map($event, EventDTO::class);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $events
        ]);
    }
}
