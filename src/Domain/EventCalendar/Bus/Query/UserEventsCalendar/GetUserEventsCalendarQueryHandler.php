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

namespace App\Domain\EventCalendar\Bus\Query\UserEventsCalendar;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\EventCalendar\Facade\EventCalendarFacade;

readonly class GetUserEventsCalendarQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventCalendarFacade $eventCalendarFacade) {}

    public function __invoke(GetUserEventsCalendarQuery $query): GetUserEventsCalendarQueryResult
    {
        $events = $this->eventCalendarFacade->convertUserEventsToArray(
            event: $query->event,
            userEvents: $query->userEvents
        );

        return new GetUserEventsCalendarQueryResult($events);
    }
}
