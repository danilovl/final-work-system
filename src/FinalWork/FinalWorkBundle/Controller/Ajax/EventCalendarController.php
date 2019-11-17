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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use DateTime;
use FinalWork\FinalWorkBundle\Controller\Middleware\EventCalendar\Ajax\{
    EditMiddleware,
    GetEventMiddleware
};
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use Exception;
use FinalWork\FinalWorkBundle\Exception\AjaxRuntimeException;
use FinalWork\FinalWorkBundle\Model\Event\EventModel;
use FinalWork\FinalWorkBundle\Model\EventWorkReservation\EventWorkReservationModel;
use FinalWork\FinalWorkBundle\Constant\{
    WorkStatusConstant,
    AjaxJsonTypeConstant,
    VoterSupportConstant,
    WorkUserTypeConstant
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    Work,
    WorkStatus,
    EventParticipant
};
use FinalWork\FinalWorkBundle\Form\{
    EventForm,
    EventWorkReservationForm
};
use FinalWork\FinalWorkBundle\Helper\{
    SortFunctionHelper,
    FormValidationMessageHelper
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventCalendarController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws Exception
     */
    public function getEventAction(Request $request): JsonResponse
    {
        try {
            GetEventMiddleware::handle($request);

            $startDate = new DateTime($request->get('start'));
            $endDate = new DateTime($request->get('end'));

            $events = $this->get('final_work.facade.event_calendar')
                ->getEventsByOwner(
                    $this->getUser(),
                    $request->get('type'),
                    $startDate, $endDate
                );
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
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

            $event = $this->get('final_work.factory.event')
                ->flushFromModel($eventModel);

            $eventParticipant = $eventModel->participant;
            if ($eventParticipant !== null) {
                $eventParticipant->setEvent($event);
                $this->flushEntity();
                $event->setParticipant($eventParticipant);
            }

            $this->get('final_work.event_dispatcher.event')
                ->onEventCalendarCreate($event, $eventParticipant !== null);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
                'data' => FormValidationMessageHelper::getErrorMessages($form)
            ]);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $this->get('final_work.facade.event_calendar')->convertEventCreateToArray($event),
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function eventReservationAction(
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

            $this->get('final_work.event_dispatcher.event')
                ->onEventCalendarReservation($event);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE);
        }

        $eventCalendar = $this->get('final_work.facade.event_calendar')
            ->convertEventReservationToArray($event);

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'event' => $eventCalendar,
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function editAction(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        try {
            EditMiddleware::handle($request);

            $event->setStart(new DateTime($request->get('start')));
            $event->setEnd(new DateTime($request->get('end')));

            $this->flushEntity();

            $this->get('final_work.event_dispatcher.event')
                ->onEventCalendarEdit($event);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS, [
            'event_id' => $this->hashIdEncode($event->getId())
        ]);
    }
}
