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

namespace FinalWork\FinalWorkBundle\Model\Event;

use DateTime;
use Doctrine\ORM\{
    EntityManager,
    ORMException
};
use FinalWork\FinalWorkBundle\Constant\{
    EventTypeConstant,
    WorkStatusConstant,
    WorkUserTypeConstant,
    EventCalendarActionTypeConstant
};
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Event,
    EventType,
    WorkStatus
};
use FinalWork\FinalWorkBundle\Entity\Repository\EventRepository;
use FinalWork\FinalWorkBundle\Helper\DateHelper;
use FinalWork\SonataUserBundle\Entity\User;
use Roukmoute\HashidsBundle\Hashids;
use Symfony\Component\Routing\Router;

class EventCalendarFacade
{
    /**
     * @var string
     */
    private const CALENDAR_EVENT_RESERVED_COLOR = '#f00';

    /**
     * @var string
     */
    private const CALENDAR_EVENT_DETAIL_RESERVED_COLOR = '#4bd44d';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Hashids
     */
    private $hashIds;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * EventCalendarFacade constructor.
     * @param EntityManager $entityManager
     * @param Router $router
     * @param Hashids $hashIds
     */
    public function __construct(
        EntityManager $entityManager,
        Router $router,
        Hashids $hashIds
    ) {
        $this->em = $entityManager;
        $this->router = $router;
        $this->hashIds = $hashIds;
        $this->eventRepository = $entityManager->getRepository(Event::class);
    }

    /**
     * @param User $user
     * @param string $type
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array
     *
     * @throws ORMException
     */
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
                    ->findAllByOwner($user, $startDate, $endDate)
                    ->getQuery()
                    ->getResult();

                /** @var Event $appointment */
                foreach ($userEvents as $appointment) {
                    $event = [];
                    $event['id'] = $this->hashIds->encode($appointment->getId());
                    $event['title'] = (string)$appointment;
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
                        ->findAllByOwner(
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
                                $event['title'] = $event['title'] . "\n" . (string)$participant;
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

    /**
     * @param Event $event
     * @return array
     */
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

    /**
     * @param Event $event
     * @return array
     */
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

    /**
     * @param Event $event
     * @param array $userEvents
     * @return array
     */
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

    /**
     * @param Event $event
     * @return array
     */
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
