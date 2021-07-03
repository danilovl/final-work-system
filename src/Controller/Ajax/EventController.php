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
use App\DataTransferObject\Repository\EventData;
use DateTime;
use App\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Controller\BaseController;
use App\Entity\{
    Event,
    EventParticipant
};
use App\Form\EventForm;
use App\Helper\FormValidationMessageHelper;
use App\Model\Event\EventModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventController extends BaseController
{
    #[AjaxRequestMiddlewareAttribute([
        'class' => 'App\Middleware\Event\Ajax\GetEventMiddleware'
    ])]
    public function getEvent(
        Request $request,
        Event $event
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        $user = $this->getUser();
        $eventService = $this->get('app.facade.event');

        $mediaData = EventData::createFromArray([
            'user' => $user,
            'startDate' => new DateTime($request->get('start')),
            'endDate' => new DateTime($request->get('end'))
        ]);

        if ($event->isOwner($user)) {
            $userEvents = $eventService->getEventsByOwner($mediaData);
        } else {
            $userEvents = $eventService->getEventsByParticipant($mediaData);
        }

        $events = $this->get('app.facade.event_calendar')
            ->convertUserEventsToArray($event, $userEvents);

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT)
        ]);
    }

    public function edit(
        Request $request,
        Event $event
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        $origin = clone $event;
        $user = $this->getUser();
        $eventModel = EventModel::fromEvent($event);

        $form = $this->createForm(EventForm::class, $eventModel, [
            'addresses' => $user->getEventAddressOwner(),
            'participants' => $this->get('app.facade.event_participant')
                ->getEventParticipantsByUserEvent($user, $event)
        ]);
        $form->get('participant')->isRequired();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventParticipantForm = $eventModel->participant;

            $eventParticipant = $origin->getParticipant() ?? new EventParticipant;
            if ($eventParticipantForm !== null) {
                $eventParticipant->setWork($eventParticipantForm->getWork());
                $eventParticipant->setUser($eventParticipantForm->getUser());
                $eventParticipant->setEvent($event);
                $eventModel->participant = $eventParticipant;
            } elseif ($eventParticipant->getId() !== null) {
                $this->removeEntity($eventParticipant);
                $eventModel->participant = null;
            }

            $this->get('app.factory.event')->flushFromModel($eventModel, $event);
            $this->get('app.event_dispatcher.event')->onEventEdit($event);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    public function delete(Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        $this->removeEntity($event);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
