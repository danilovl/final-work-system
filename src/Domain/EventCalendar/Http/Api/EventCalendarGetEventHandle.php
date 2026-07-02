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

namespace App\Domain\EventCalendar\Http\Api;

use App\Domain\Event\Facade\EventCalendarFacade;
use App\Domain\EventCalendar\DTO\Api\EventCalendarDTO;
use App\Domain\User\Service\UserService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventCalendarGetEventHandle
{
    public function __construct(
        private EventCalendarFacade $eventCalendarFacade,
        private UserService $userService
    ) {}

    public function __invoke(string $type, string $start, string $end): JsonResponse
    {
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);

        $events = $this->eventCalendarFacade->listEventsByOwner(
            user: $this->userService->getUser(),
            type: $type,
            startDate: $startDate,
            endDate: $endDate,
            isApi: true
        );

        $eventDTOs = [];
     
        foreach ($events as $event) {
            $eventDTOs[] = new EventCalendarDTO(
                id: (int) $event['id'],
                title: $event['title'] ?? 'no title',
                color: $event['color'],
                start: $event['start'],
                end: $event['end'],
                hasParticipant: $event['hasParticipant']
            );
        }

        return new JsonResponse($eventDTOs);
    }
}
