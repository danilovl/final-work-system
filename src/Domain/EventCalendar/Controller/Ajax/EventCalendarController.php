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

namespace App\Domain\EventCalendar\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Middleware\EventCalendar\Ajax\{
    EditMiddleware,
    GetEventMiddleware
};
use App\Infrastructure\Service\AuthorizationCheckerService;
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
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class EventCalendarController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private EventCalendarGetEventHandle $eventCalendarGetEventHandle,
        private EventCalendarCreateHandle $eventCalendarCreateHandle,
        private EventCalendarEventReservationHandle $eventCalendarEventReservationHandle,
        private EventCalendarEditHandle $eventCalendarEditHandle
    ) {}

    #[PermissionMiddleware(service: [
        'name' => GetEventMiddleware::class
    ])]
    public function getEvent(GetEventRequest $request): JsonResponse
    {
        return $this->eventCalendarGetEventHandle->__invoke($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->eventCalendarCreateHandle->__invoke($request);
    }

    public function eventReservation(Request $request, Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION->value, $event);

        return $this->eventCalendarEventReservationHandle->__invoke($request, $event);
    }

    #[PermissionMiddleware(service: [
        'name' => EditMiddleware::class
    ])]
    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::EDIT->value, $event);

        return $this->eventCalendarEditHandle->__invoke($request, $event);
    }
}
