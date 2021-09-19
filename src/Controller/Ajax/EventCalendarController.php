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
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventCalendarController extends BaseController
{
    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\EventCalendar\Ajax\GetEventMiddleware'
    ])]
    public function getEvent(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.event_calendar.get_event')->handle($request);
    }

    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.event_calendar.create')->handle($request);
    }

    public function eventReservation(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION, $event);

        return $this->get('app.http_handle_ajax.event_calendar.event_reservation')->handle($request, $event);
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\EventCalendar\Ajax\EditMiddleware'
    ])]
    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        return $this->get('app.http_handle_ajax.event_calendar.edit')->handle($request, $event);
    }
}
