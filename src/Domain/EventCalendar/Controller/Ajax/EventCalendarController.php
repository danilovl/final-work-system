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

namespace App\Domain\EventCalendar\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Middleware\EventCalendar\Ajax\EditMiddleware;
use App\Application\Middleware\EventCalendar\Ajax\GetEventMiddleware;
use App\Domain\Event\Entity\Event;
use App\Domain\EventCalendar\Request\GetEventRequest;
use Danilovl\PermissionMiddlewareBundle\Attribute\PermissionMiddleware;
use App\Domain\EventCalendar\Http\Ajax\{
    EventCalendarEditHandle,
    EventCalendarCreateHandle,
    EventCalendarGetEventHandle,
    EventCalendarEventReservationHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventCalendarController extends AbstractController
{
    public function __construct(
        private readonly EventCalendarGetEventHandle $eventCalendarGetEventHandle,
        private readonly EventCalendarCreateHandle $eventCalendarCreateHandle,
        private readonly EventCalendarEventReservationHandle $eventCalendarEventReservationHandle,
        private readonly EventCalendarEditHandle $eventCalendarEditHandle
    ) {}

    #[PermissionMiddleware(service: [
        'name' => GetEventMiddleware::class
    ])]
    public function getEvent(GetEventRequest $request): JsonResponse
    {
        return $this->eventCalendarGetEventHandle->handle($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->eventCalendarCreateHandle->handle($request);
    }

    public function eventReservation(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION->value, $event);

        return $this->eventCalendarEventReservationHandle->handle($request, $event);
    }

    #[PermissionMiddleware(service: [
        'name' => EditMiddleware::class
    ])]
    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $event);

        return $this->eventCalendarEditHandle->handle($request, $event);
    }
}
