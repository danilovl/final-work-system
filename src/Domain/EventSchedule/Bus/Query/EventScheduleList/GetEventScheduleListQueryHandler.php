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

namespace App\Domain\EventSchedule\Bus\Query\EventScheduleList;

use App\Infrastructure\Service\PaginatorService;
use App\Domain\EventSchedule\Facade\EventScheduleFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetEventScheduleListQueryHandler
{
    public function __construct(
        private EventScheduleFacade $eventScheduleFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetEventScheduleListQuery $query): GetEventScheduleListQueryResult
    {
        $eventSchedulesQuery = $this->eventScheduleFacade->queryByOwner($query->user);
        $pagination = $this->paginatorService->createPaginationRequest($query->request, $eventSchedulesQuery);

        return new GetEventScheduleListQueryResult($pagination);
    }
}
