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

namespace App\Model\Event;

use App\DataTransferObject\Repository\EventData;
use App\Service\EntityManagerService;
use App\Service\UserWorkService;
use Danilovl\HashidsBundle\Services\HashidsService;
use Danilovl\ParameterBundle\Services\ParameterService;
use DateTime;
use App\Constant\{
    DateFormatConstant,
    EventTypeConstant,
    WorkStatusConstant,
    WorkUserTypeConstant,
    EventCalendarActionTypeConstant
};
use App\Exception\ConstantNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\{
    Work,
    Event,
    EventType,
    WorkStatus
};
use App\Repository\EventRepository;
use App\Helper\DateHelper;
use App\Entity\User;

class EventCalendarFacade
{
    private string $calendarEventReservedColor;
    private string $calendarEventDetailReservedColor;

    private EventRepository $eventRepository;

    public function __construct(
        private UserWorkService $userWorkService,
        private EntityManagerService $entityManager,
        private RouterInterface $router,
        private HashidsService $hashIds,
        private ParameterService $parameterService
    ) {
        $this->eventRepository = $entityManager->getRepository(Event::class);

        $this->calendarEventReservedColor = $parameterService->get('event_calendar.reserved_color');
        $this->calendarEventDetailReservedColor = $parameterService->get('event_calendar.detail_reserved_color');
    }

    public function getEventsByOwner(
        User $user,
        string $type,
        Datetime $startDate,
        Datetime $endDate
    ): array {
        $events = [];

        switch ($type) {
            case EventCalendarActionTypeConstant::MANAGE:
                $mediaData = EventData::createFromArray([
                    'user' => $user,
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]);

                $userEvents = $this->eventRepository
                    ->allByOwner($mediaData)
                    ->getQuery()
                    ->getResult();

                /** @var Event $appointment */
                foreach ($userEvents as $appointment) {
                    $event = [];
                    $event['id'] = $this->hashIds->encode($appointment->getId());
                    $event['title'] = (string) $appointment;
                    $event['color'] = $appointment->getType()->getColor();
                    $event['start'] = $appointment->getStart()->format(DateFormatConstant::DATABASE);
                    $event['end'] = $appointment->getEnd()->format(DateFormatConstant::DATABASE);
                    $event['detail_url'] = $this->router->generate('event_detail', [
                        'id' => $this->hashIds->encode($appointment->getId())
                    ]);
                    $event['delete_url'] = $this->router->generate('event_delete_ajax', [
                        'id' => $this->hashIds->encode($appointment->getId())
                    ]);

                    $participant = $appointment->getParticipant();

                    if ($participant) {
                        $event['color'] = $this->calendarEventReservedColor;
                    }

                    $events[] = $event;
                }
                break;
            case EventCalendarActionTypeConstant::RESERVATION:
                $userWorks = $this->userWorkService->getWorkBy(
                    $user,
                    WorkUserTypeConstant::AUTHOR,
                    null,
                    $this->entityManager->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
                );

                $supervisors = [];
                /** @var Work $userWork */
                foreach ($userWorks as $userWork) {
                    $supervisor = $userWork->getSupervisor();
                    if ($user->getId() !== $supervisor->getId() &&
                        !in_array($supervisor, $supervisors, true)
                    ) {
                        $supervisors[] = $supervisor;
                    }
                }

                /** @var User $supervisor */
                foreach ($supervisors as $supervisor) {
                    $mediaData = EventData::createFromArray([
                        'user' => $supervisor,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'eventType' => $this->entityManager->getReference(EventType::class, EventTypeConstant::CONSULTATION)
                    ]);

                    $supervisorAppointments = $this->eventRepository
                        ->allByOwner($mediaData)
                        ->getQuery()
                        ->getResult();

                    /** @var Event $supervisorAppointment */
                    foreach ($supervisorAppointments as $supervisorAppointment) {
                        $event = [];
                        $event['id'] = $this->hashIds->encode($supervisorAppointment->getId());
                        $event['start'] = $supervisorAppointment->getStart()->format(DateFormatConstant::DATABASE);
                        $event['end'] = $supervisorAppointment->getEnd()->format(DateFormatConstant::DATABASE);

                        if ($supervisorAppointment->getAddress()) {
                            $event['title'] = $supervisorAppointment->getAddress()->getName() . "\n" . $supervisorAppointment->getOwner();
                        } else {
                            $event['title'] = $supervisorAppointment->getOwner();
                        }

                        $participant = $supervisorAppointment->getParticipant();
                        if ($participant) {
                            if ($participant->getUser()->getId() === $user->getId()) {
                                $event['color'] = $this->calendarEventReservedColor;
                                $event['title'] = $event['title'] . "\n" . (string) $participant;
                                $event['detail_url'] = $this->router->generate('event_detail', [
                                    'id' => $this->hashIds->encode($supervisorAppointment->getId())
                                ]);
                            } else {
                                continue;
                            }
                        } else {
                            if (DateHelper::actualDay() > $supervisorAppointment->getStart()->format(DateFormatConstant::DATABASE)) {
                                continue;
                            }
                            $event['reservation_url'] = $this->router->generate('event_calendar_reservation_ajax', [
                                'id' => $this->hashIds->encode($supervisorAppointment->getId())
                            ]);
                        }

                        $events[] = $event;
                    }
                }
                break;
            default:
                throw new ConstantNotFoundException('Event calendar type constant not found');
        }

        return $events;
    }

    public function convertEventCreateToArray(Event $event): array
    {
        $eventCalendar = $this->baseEventArray($event);

        $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
            'id' => $this->hashIds->encode($event->getId())
        ]);
        $eventCalendar['delete_url'] = $this->router->generate('event_delete_ajax', [
            'id' => $this->hashIds->encode($event->getId())
        ]);

        if ($event->getParticipant() !== null) {
            $eventCalendar['color'] = $this->calendarEventReservedColor;
            $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
                'id' => $this->hashIds->encode($event->getId())
            ]);
        }

        return $eventCalendar;
    }

    public function convertEventReservationToArray(Event $event): array
    {
        $eventCalendar = $this->baseEventArray($event);

        $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
            'id' => $this->hashIds->encode($event->getId())
        ]);
        $eventCalendar['delete_url'] = $this->router->generate('event_delete_ajax', [
            'id' => $this->hashIds->encode($event->getId())
        ]);

        if ($event->getParticipant() !== null) {
            $eventCalendar['color'] = $this->calendarEventReservedColor;
        }

        return $eventCalendar;
    }

    public function convertUserEventsToArray(Event $event, array $userEvents): array
    {
        $events = [];

        /** @var Event $userEvent */
        foreach ($userEvents as $userEvent) {
            $eventCalendar = $this->baseEventArray($userEvent);

            if ($userEvent->getParticipant() !== null) {
                $eventCalendar['color'] = $this->calendarEventReservedColor;
                $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
                    'id' => $this->hashIds->encode($userEvent->getId())
                ]);
            }

            if ($userEvent->getId() === $event->getId()) {
                $eventCalendar['color'] = $this->calendarEventDetailReservedColor;
            }

            $events[] = $eventCalendar;
        }

        return $events;
    }

    private function baseEventArray(Event $event): array
    {
        return [
            'id' => $this->hashIds->encode($event->getId()),
            'title' => $event->toString(),
            'color' => $event->getType()->getColor(),
            'start' => $event->getStart()->format(DateFormatConstant::DATABASE),
            'end' => $event->getEnd()->format(DateFormatConstant::DATABASE)
        ];
    }
}
