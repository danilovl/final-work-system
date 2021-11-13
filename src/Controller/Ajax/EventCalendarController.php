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

namespace App\Controller\Ajax;

use App\Attribute\AjaxRequestMiddlewareAttribute;
use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\Event;
use App\Model\EventCalendar\Http\Ajax\{
    EventCalendarEditHandle,
    EventCalendarCreateHandle,
    EventCalendarGetEventHandle,
    EventCalendarEventReservationHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventCalendarController extends BaseController
{
    public function __construct(
        private EventCalendarGetEventHandle $eventCalendarGetEventHandle,
        private EventCalendarCreateHandle $eventCalendarCreateHandle,
        private EventCalendarEventReservationHandle $eventCalendarEventReservationHandle,
        private EventCalendarEditHandle $eventCalendarEditHandle
    ) {
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\EventCalendar\Ajax\GetEventMiddleware'
    ])]
    public function getEvent(Request $request): JsonResponse
    {
        return $this->eventCalendarGetEventHandle->handle($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->eventCalendarCreateHandle->handle($request);
    }

    public function eventReservation(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION, $event);

        return $this->eventCalendarEventReservationHandle->handle($request, $event);
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\EventCalendar\Ajax\EditMiddleware'
    ])]
    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        return $this->eventCalendarEditHandle->handle($request, $event);
    }
}
