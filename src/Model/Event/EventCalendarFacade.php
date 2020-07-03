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

use App\Services\EntityManagerService;
use DateTime;
use App\Constant\{
    EventTypeConstant,
    WorkStatusConstant,
    WorkUserTypeConstant,
    EventCalendarActionTypeConstant
};
use App\Exception\ConstantNotFoundException;
use Hashids\HashidsInterface;
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
    private const CALENDAR_EVENT_RESERVED_COLOR = '#f00';
    private const CALENDAR_EVENT_DETAIL_RESERVED_COLOR = '#4bd44d';

    private EntityManagerService $em;
    private RouterInterface $router;
    private HashidsInterface $hashIds;
    private EventRepository $eventRepository;

    public function __construct(
        EntityManagerService $entityManager,
        RouterInterface $router,
        HashidsInterface $hashIds
    ) {
        $this->em = $entityManager;
        $this->router = $router;
        $this->hashIds = $hashIds;
        $this->eventRepository = $entityManager->getRepository(Event::class);
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
                $userEvents = $this->eventRepository
                    ->allByOwner($user, $startDate, $endDate)
                    ->getQuery()
                    ->getResult();

                /** @var Event $appointment */
                foreach ($userEvents as $appointment) {
                    $event = [];
                    $event['id'] = $this->hashIds->encode($appointment->getId());
                    $event['title'] = (string) $appointment;
                    $event['color'] = $appointment->getType()->getColor();
                    $event['start'] = $appointment->getStart()->format('Y-m-d H:i:s');
                    $event['end'] = $appointment->getEnd()->format('Y-m-d H:i:s');
                    $event['detail_url'] = $this->router->generate('event_detail', [
                        'id' => $this->hashIds->encode($appointment->getId())
                    ]);
                    $event['delete_url'] = $this->router->generate('event_delete_ajax', [
                        'id' => $this->hashIds->encode($appointment->getId())
                    ]);

                    $participant = $appointment->getParticipant();

                    if ($participant) {
                        $event['color'] = self::CALENDAR_EVENT_RESERVED_COLOR;
                    }

                    $events[] = $event;
                }
                break;
            case EventCalendarActionTypeConstant::RESERVATION:
                $userWorks = $user->getWorkBy(
                    WorkUserTypeConstant::AUTHOR,
                    null,
                    $this->em->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE)
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
                    $supervisorAppointments = $this->eventRepository
                        ->allByOwner(
                            $supervisor,
                            $startDate,
                            $endDate,
                            $this->em->getReference(EventType::class, EventTypeConstant::CONSULTATION)
                        )
                        ->getQuery()
                        ->getResult();

                    /** @var Event $supervisorAppointment */
                    foreach ($supervisorAppointments as $supervisorAppointment) {
                        $event = [];
                        $event['id'] = $this->hashIds->encode($supervisorAppointment->getId());
                        $event['start'] = $supervisorAppointment->getStart()->format('Y-m-d H:i:s');
                        $event['end'] = $supervisorAppointment->getEnd()->format('Y-m-d H:i:s');

                        if ($supervisorAppointment->getAddress()) {
                            $event['title'] = $supervisorAppointment->getAddress()->getName() . "\n" . $supervisorAppointment->getOwner();
                        } else {
                            $event['title'] = $supervisorAppointment->getOwner();
                        }

                        $participant = $supervisorAppointment->getParticipant();
                        if ($participant) {
                            if ($participant->getUser()->getId() === $user->getId()) {
                                $event['color'] = self::CALENDAR_EVENT_RESERVED_COLOR;
                                $event['title'] = $event['title'] . "\n" . (string) $participant;
                                $event['detail_url'] = $this->router->generate('event_detail', [
                                    'id' => $this->hashIds->encode($supervisorAppointment->getId())
                                ]);
                            } else {
                                continue;
                            }
                        } else {
                            if (DateHelper::actualDay() > $supervisorAppointment->getStart()->format('Y-m-d H:i:s')) {
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
            $eventCalendar['color'] = self::CALENDAR_EVENT_RESERVED_COLOR;
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

        $participant = $event->getParticipant();

        if ($participant) {
            $eventCalendar['color'] = self::CALENDAR_EVENT_RESERVED_COLOR;
        }

        return $eventCalendar;
    }

    public function convertUserEventsToArray(Event $event, array $userEvents): array
    {
        $events = [];

        /** @var Event $userEvent */
        foreach ($userEvents as $userEvent) {
            $eventCalendar = $this->baseEventArray($userEvent);

            $participant = $userEvent->getParticipant();

            if ($participant) {
                $eventCalendar['color'] = self::CALENDAR_EVENT_RESERVED_COLOR;
                $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
                    'id' => $this->hashIds->encode($userEvent->getId())
                ]);
            }

            if ($userEvent->getId() === $event->getId()) {
                $eventCalendar['color'] = self::CALENDAR_EVENT_DETAIL_RESERVED_COLOR;
            }

            $events[] = $eventCalendar;
        }

        return $events;
    }

    private function baseEventArray(Event $event): array
    {
        $eventCalendar = [];

        $eventCalendar['id'] = $this->hashIds->encode($event->getId());
        $eventCalendar['title'] = $event->toString();
        $eventCalendar['color'] = $event->getType()->getColor();
        $eventCalendar['start'] = $event->getStart()->format('Y-m-d H:i:s');
        $eventCalendar['end'] = $event->getEnd()->format('Y-m-d H:i:s');

        return $eventCalendar;
    }
}
