<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\EventCalendar\Controller;

use App\Domain\EventCalendar\Http\{
    EventCalendarManageHandle,
    EventCalendarReservationHandle
};
use Symfony\Component\HttpFoundation\Response;

readonly class EventCalendarController
{
    public function __construct(
        private EventCalendarReservationHandle $eventCalendarReservationHandle,
        private EventCalendarManageHandle $eventCalendarManageHandle
    ) {}

    public function reservation(): Response
    {
        return $this->eventCalendarReservationHandle->handle();
    }

    public function manage(): Response
    {
        return $this->eventCalendarManageHandle->handle();
    }
}
