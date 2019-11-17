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
use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use Doctrine\ORM\OptimisticLockException;
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    EventParticipant
};
use FinalWork\FinalWorkBundle\Controller\Middleware\Event\Ajax\GetEventMiddleware;
use FinalWork\FinalWorkBundle\Exception\AjaxRuntimeException;
use FinalWork\FinalWorkBundle\Form\EventForm;
use FinalWork\FinalWorkBundle\Helper\FormValidationMessageHelper;
use FinalWork\FinalWorkBundle\Model\Event\EventModel;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Exception;
use Doctrine\ORM\ORMException;

class EventController extends BaseController
{
    /**
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getEventAction(
        Request $request,
        Event $event
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $event);

        try {
            GetEventMiddleware::handle($request);
        } catch (AjaxRuntimeException $exception) {
            return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE);
        }

        $user = $this->getUser();
        $eventService = $this->get('final_work.facade.event');

        $startDate = new DateTime($request->get('start'));
        $endDate = new DateTime($request->get('end'));

        if ($event->isOwner($user)) {
            $userEvents = $eventService->getEventsByOwner($user, $startDate, $endDate);
        } else {
            $userEvents = $eventService->getEventsByParticipant($user, $startDate, $endDate);
        }

        $events = $this->get('final_work.facade.event_calendar')
            ->convertUserEventsToArray($event, $userEvents);

        return $this->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, [
            'events' => json_encode($events, JSON_PRETTY_PRINT)
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return JsonResponse
     *
     * @throws ORMException
     */
    public function editAction(
        Request $request,
        Event $event
    ): JsonResponse {
        $this->denyAccessUnlessGranted(VoterSupportConstant::EDIT, $event);

        $user = $this->getUser();
        $eventModel = EventModel::fromEvent($event);

        $form = $this->createForm(EventForm::class, $eventModel, [
            'addresses' => $user->getEventAddressOwner(),
            'participants' => $this->get('final_work.facade.event_participant')
                ->getEventParticipantsByUserEvent($user, $event)
        ]);
        $form->get('participant')->isRequired();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventParticipantForm = $eventModel->participant;

            $eventParticipant = $event->getParticipant() ?? new EventParticipant;
            if ($eventParticipantForm) {
                $eventParticipant->setWork($eventParticipantForm->getWork());
                $eventParticipant->setUser($eventParticipantForm->getUser());
                $eventParticipant->setEvent($event);
                $eventModel->participant = $eventParticipant;
            } elseif ($eventParticipant->getId()) {
                $this->removeEntity($eventParticipant);
                $eventModel->participant = null;
            }

            $this->get('final_work.factory.event')->flushFromModel($eventModel, $event);
            $this->get('final_work.event_dispatcher.event')->onEventEdit($event);

            return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }

    /**
     * @param Event $event
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::DELETE, $event);

        $this->removeEntity($event);

        return $this->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
