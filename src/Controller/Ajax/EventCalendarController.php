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
use DateTime;
use App\Exception\AjaxRuntimeException;
use App\Model\Event\EventModel;
use App\Model\EventWorkReservation\EventWorkReservationModel;
use App\Constant\{
    WorkStatusConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant,
    WorkUserTypeConstant
};
use App\Controller\BaseController;
use App\Entity\{
    Event,
    Work,
    WorkStatus,
    EventParticipant
};
use App\Form\{
    EventForm,
    EventWorkReservationForm
};
use App\Helper\{
    SortFunctionHelper,
    FormValidationMessageHelper
};
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
        $startDate = new DateTime($request->get('start'));
        $endDate = new DateTime($request->get('end'));

        $events = $this->get('app.facade.event_calendar')->getEventsByOwner(
            $this->getUser(),
            $request->get('type'),
            $startDate,
            $endDate
        );

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $userWorks = $user->getWorkBy(
            WorkUserTypeConstant::SUPERVISOR,
            null,
            $this->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );
        $eventParticipantArray = [];

        /** @var Work $work */
        foreach ($userWorks as $work) {
            $eventParticipant = new EventParticipant;
            $eventParticipant->setUser($work->getAuthor());
            $eventParticipant->setWork($work);
            $eventParticipantArray[] = $eventParticipant;
        }

        SortFunctionHelper::eventParticipantSort($eventParticipantArray);

        $eventModel = new EventModel;
        $eventModel->owner = $user;

        $form = $this
            ->createForm(EventForm::class, $eventModel, [
                'addresses' => $user->getEventAddressOwner(),
                'participants' => $eventParticipantArray
            ])
            ->handleRequest($request);

        try {
            if ($form->isSubmitted() && !$form->isValid()) {
                throw new AjaxRuntimeException('Form not valid');
            }

            $event = $this->get('app.factory.event')
                ->flushFromModel($eventModel);

            $eventParticipant = $eventModel->participant;
            if ($eventParticipant !== null) {
                $eventParticipant->setEvent($event);
                $this->flushEntity();
                $event->setParticipant($eventParticipant);
            }

            $this->get('app.event_dispatcher.event')
                ->onEventCalendarCreate($event, $eventParticipant !== null);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                'data' => FormValidationMessageHelper::getErrorMessages($form)
            ]);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $this->get('app.facade.event_calendar')->convertEventCreateToArray($event),
        ]);
    }

    public function eventReservation(
        Request $request,
        Event $event
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::RESERVATION, $event);

        $user = $this->getUser();
        $userWorks = $user->getWorkBy(
            WorkUserTypeConstant::AUTHOR,
            null,
            $this->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
        );

        $eventWorkReservationModel = new EventWorkReservationModel;
        $form = $this
            ->createForm(EventWorkReservationForm::class, $eventWorkReservationModel, [
                'works' => $userWorks
            ])
            ->handleRequest($request);

        try {
            if ($form->isSubmitted() && !$form->isValid()) {
                throw new AjaxRuntimeException('Form not valid');
            }

            $work = $eventWorkReservationModel->work;
            $ownerWorks = $event->getOwner()
                ->getSupervisorWorks();

            if ($ownerWorks->contains($work) === false) {
                throw new AjaxRuntimeException('Work');
            }

            $appointmentParticipant = new EventParticipant;
            $appointmentParticipant->setEvent($event);
            $appointmentParticipant->setUser($user);
            $appointmentParticipant->setWork($work);

            $this->createEntity($appointmentParticipant);
            $event->setParticipant($appointmentParticipant);

            $this->get('app.event_dispatcher.event')
                ->onEventCalendarReservation($event);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE);
        }

        $eventCalendar = $this->get('app.facade.event_calendar')
            ->convertEventReservationToArray($event);

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $eventCalendar,
        ]);
    }

    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\EventCalendar\Ajax\EditMiddleware'
    ])]
    public function edit(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        $event->setStart(new DateTime($request->get('start')));
        $event->setEnd(new DateTime($request->get('end')));

        $this->flushEntity();

        $this->get('app.event_dispatcher.event')
            ->onEventCalendarEdit($event);

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS, [
            'event_id' => $this->hashIdEncode($event->getId())
        ]);
    }
}
