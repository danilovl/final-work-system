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

namespace App\Domain\Event\Facade;

use App\Application\Constant\DateFormatConstant;
use App\Application\Exception\ConstantNotFoundException;
use App\Application\Helper\DateHelper;
use App\Application\Service\EntityManagerService;
use App\Domain\Event\DataTransferObject\EventRepositoryData;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\EventCalendar\Constant\EventCalendarActionTypeConstant;
use App\Domain\EventType\Constant\EventTypeConstant;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use DateTime;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

class EventCalendarFacade
{
    private string $calendarEventReservedColor;

    private string $calendarEventDetailReservedColor;

    public function __construct(
        private readonly UserWorkService $userWorkService,
        private readonly EntityManagerService $entityManager,
        private readonly RouterInterface $router,
        private readonly HashidsServiceInterface $hashIds,
        private readonly EventRepository $eventRepository,
        ParameterServiceInterface $parameterService
    ) {
        $this->calendarEventReservedColor = $parameterService->getString('event_calendar.reserved_color');
        $this->calendarEventDetailReservedColor = $parameterService->getString('event_calendar.detail_reserved_color');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEventsByOwner(
        User $user,
        string $type,
        DateTime $startDate,
        DateTime $endDate
    ): array {
        $events = [];

        switch ($type) {
            case EventCalendarActionTypeConstant::MANAGE->value:
                $mediaData = EventRepositoryData::createFromArray([
                    'user' => $user,
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]);

                /** @var Event[] $userEvents */
                $userEvents = $this->eventRepository
                    ->allByOwner($mediaData)
                    ->getQuery()
                    ->getResult();

                foreach ($userEvents as $appointment) {
                    $event = [];
                    $event['id'] = $this->hashIds->encode($appointment->getId());
                    $event['title'] = (string) $appointment;
                    $event['color'] = $appointment->getType()->getColor();
                    $event['start'] = $appointment->getStart()->format(DateFormatConstant::DATABASE->value);
                    $event['end'] = $appointment->getEnd()->format(DateFormatConstant::DATABASE->value);
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
            case EventCalendarActionTypeConstant::RESERVATION->value:
                /** @var WorkStatus $workStatus */
                $workStatus = $this->entityManager->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE->value);

                /** @var Work[] $userWorks */
                $userWorks = $this->userWorkService->getWorkBy(
                    $user,
                    WorkUserTypeConstant::AUTHOR->value,
                    null,
                    $workStatus
                );

                $supervisors = [];
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
                    $mediaData = EventRepositoryData::createFromArray([
                        'user' => $supervisor,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'eventType' => $this->entityManager->getReference(EventType::class, EventTypeConstant::CONSULTATION->value)
                    ]);

                    /** @var Event[] $supervisorAppointments */
                    $supervisorAppointments = $this->eventRepository
                        ->allByOwner($mediaData)
                        ->getQuery()
                        ->getResult();

                    foreach ($supervisorAppointments as $supervisorAppointment) {
                        $event = [];
                        $event['id'] = $this->hashIds->encode($supervisorAppointment->getId());
                        $event['start'] = $supervisorAppointment->getStart()->format(DateFormatConstant::DATABASE->value);
                        $event['end'] = $supervisorAppointment->getEnd()->format(DateFormatConstant::DATABASE->value);

                        if ($supervisorAppointment->getAddress()) {
                            $event['title'] = $supervisorAppointment->getAddress()->getName() . "\n" . $supervisorAppointment->getOwner();
                        } else {
                            $event['title'] = $supervisorAppointment->getOwner();
                        }

                        $participant = $supervisorAppointment->getParticipant();
                        if ($participant) {
                            if ($participant->getUserMust()->getId() === $user->getId()) {
                                $event['color'] = $this->calendarEventReservedColor;
                                $event['title'] = $event['title'] . "\n" . $participant->toString();
                                $event['detail_url'] = $this->router->generate('event_detail', [
                                    'id' => $this->hashIds->encode($supervisorAppointment->getId())
                                ]);
                            } else {
                                continue;
                            }
                        } else {
                            if (DateHelper::actualDay() > $supervisorAppointment->getStart()->format(DateFormatConstant::DATABASE->value)) {
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
     * @return array<string, string>
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
            $eventCalendar['color'] = $this->calendarEventReservedColor;
            $eventCalendar['detail_url'] = $this->router->generate('event_detail', [
                'id' => $this->hashIds->encode($event->getId())
            ]);
        }

        return $eventCalendar;
    }

    /**
     * @return array<string, string>
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

        if ($event->getParticipant() !== null) {
            $eventCalendar['color'] = $this->calendarEventReservedColor;
        }

        return $eventCalendar;
    }

    /**
     * @param Event[] $userEvents
     * @return array<int, array<string, mixed>>
     */
    public function convertUserEventsToArray(Event $event, array $userEvents): array
    {
        Assert::allIsInstanceOf($userEvents, Event::class);

        $events = [];

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

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     color: string,
     *     start: string,
     *     end: string
     * }
     */
    private function baseEventArray(Event $event): array
    {
        return [
            'id' => $this->hashIds->encode($event->getId()),
            'title' => $event->toString(),
            'color' => $event->getType()->getColor(),
            'start' => $event->getStart()->format(DateFormatConstant::DATABASE->value),
            'end' => $event->getEnd()->format(DateFormatConstant::DATABASE->value)
        ];
    }
}
