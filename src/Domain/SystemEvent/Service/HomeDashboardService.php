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

namespace App\Domain\SystemEvent\Service;

use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessage\Bus\Query\TotalUnreadMessagesByUser\{
    GetTotalUnreadMessagesByUserQuery,
    GetTotalUnreadMessagesByUserQueryResult
};
use App\Domain\Event\Bus\Query\EventsByOwner\{
    GetEventsByOwnerQuery,
    GetEventsByOwnerQueryResult
};
use App\Domain\Event\Bus\Query\EventsByParticipant\{
    GetEventsByParticipantQuery,
    GetEventsByParticipantQueryResult
};
use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use App\Domain\Event\Entity\Event;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\Work\Bus\Query\UserWorks\UserWorksQueryTrait;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkDeadlineHelper;
use App\Domain\WorkStatus\Bus\Query\WorkStatus\{
    GetWorkStatusQuery,
    GetWorkStatusQueryResult
};
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use DateTimeImmutable;

readonly class HomeDashboardService
{
    use UserWorksQueryTrait;

    private const int UPCOMING_DAYS = 30;

    private const int UPCOMING_LIMIT = 8;

    private const int URGENT_DAYS = 3;

    public function __construct(
        private QueryBusInterface $queryBus,
        private SystemEventFacade $systemEventFacade
    ) {}

    /**
     * @return array{
     *     activeWorksCount: int,
     *     activeWorksType: string|null,
     *     upcomingCount: int,
     *     upcoming: array<array{route: string, id: int, title: string, label: string|null, labelKey: string|null, date: DateTimeImmutable, days: int, icon: string, badgeClass: string}>,
     *     unreadMessagesCount: int,
     *     unreadAlertsCount: int
     * }
     */
    public function create(User $user): array
    {
        $works = $this->getActiveWorks($user);
        $events = $this->getUpcomingEvents($user);

        $upcoming = [
            ...$this->createWorkItems($works),
            ...$this->createEventItems($events)
        ];

        usort($upcoming, static function (array $first, array $second): int {
            $result = $first['date'] <=> $second['date'];

            return $result !== 0 ? $result : $first['id'] <=> $second['id'];
        });

        $getTotalUnreadMessagesByUserQuery = GetTotalUnreadMessagesByUserQuery::create($user);
        /** @var GetTotalUnreadMessagesByUserQueryResult $getTotalUnreadMessagesByUserQueryResult */
        $getTotalUnreadMessagesByUserQueryResult = $this->queryBus->handle($getTotalUnreadMessagesByUserQuery);

        $userTypes = $this->getUserTypes($user);
        $activeWorksType = $userTypes[0] ?? null;

        return [
            'activeWorksCount' => count($works),
            'activeWorksType' => $activeWorksType,
            'upcomingCount' => count($upcoming),
            'upcoming' => array_slice($upcoming, 0, self::UPCOMING_LIMIT),
            'unreadMessagesCount' => $getTotalUnreadMessagesByUserQueryResult->count,
            'unreadAlertsCount' => $this->systemEventFacade->getTotalUnreadSystemEventsByRecipient($user)
        ];
    }

    /**
     * @return Work[]
     */
    private function getActiveWorks(User $user): array
    {
        $getWorkStatusQuery = GetWorkStatusQuery::create(WorkStatusConstant::ACTIVE->value);
        /** @var GetWorkStatusQueryResult $getWorkStatusQueryResult */
        $getWorkStatusQueryResult = $this->queryBus->handle($getWorkStatusQuery);
        $activeStatus = $getWorkStatusQueryResult->workStatus;

        $works = [];
        $userTypes = $this->getUserTypes($user);

        foreach ($userTypes as $userType) {
            $userWorks = $this->getUserWorks(
                user: $user,
                userType: $userType,
                status: $activeStatus
            );

            foreach ($userWorks as $work) {
                $works[$work->getId()] = $work;
            }
        }

        return array_values($works);
    }

    /**
     * @return Event[]
     */
    private function getUpcomingEvents(User $user): array
    {
        $now = new DateTimeImmutable;
        $endDateModifier = sprintf('+%d days', self::UPCOMING_DAYS + 1);
        $endDate = new DateTimeImmutable('today')->modify($endDateModifier);

        $eventRepositoryDTO = new EventRepositoryDTO(
            user: $user,
            startDate: $now,
            endDate: $endDate
        );

        $getEventsByOwnerQuery = GetEventsByOwnerQuery::create($eventRepositoryDTO);
        /** @var GetEventsByOwnerQueryResult $getEventsByOwnerQueryResult */
        $getEventsByOwnerQueryResult = $this->queryBus->handle($getEventsByOwnerQuery);

        $getEventsByParticipantQuery = GetEventsByParticipantQuery::create($eventRepositoryDTO);
        /** @var GetEventsByParticipantQueryResult $getEventsByParticipantQueryResult */
        $getEventsByParticipantQueryResult = $this->queryBus->handle($getEventsByParticipantQuery);

        $events = [];
        $allEvents = [
            ...$getEventsByOwnerQueryResult->events,
            ...$getEventsByParticipantQueryResult->events
        ];

        foreach ($allEvents as $event) {
            $events[$event->getId()] = $event;
        }

        return array_values($events);
    }

    /**
     * @param Work[] $works
     * @return array<array{route: string, id: int, title: string, label: string|null, labelKey: string|null, date: DateTimeImmutable, days: int, icon: string, badgeClass: string}>
     */
    private function createWorkItems(array $works): array
    {
        $items = [];

        foreach ($works as $work) {
            $days = WorkDeadlineHelper::getDeadlineDays($work);
            if ($days < 0 || $days > self::UPCOMING_DAYS) {
                continue;
            }

            $items[] = [
                'route' => 'work_detail',
                'id' => $work->getId(),
                'title' => $work->getTitle(),
                'label' => null,
                'labelKey' => 'app.text.work_deadline',
                'date' => $work->getDeadline(),
                'days' => $days,
                'icon' => 'fa-solid fa-book',
                'badgeClass' => $this->getBadgeClass($days)
            ];
        }

        return $items;
    }

    /**
     * @param Event[] $events
     * @return array<array{route: string, id: int, title: string, label: string|null, labelKey: string|null, date: DateTimeImmutable, days: int, icon: string, badgeClass: string}>
     */
    private function createEventItems(array $events): array
    {
        $items = [];
        $today = new DateTimeImmutable('today');

        foreach ($events as $event) {
            $start = $event->getStart();
            $startDay = $start->setTime(0, 0);
            $days = (int) $today->diff($startDay)->format('%a');

            $title = $event->getName() ?? $event->getType()->getName();
            $participant = $event->getParticipant();
            $label = $participant !== null ? (string) $participant->getUser() : $event->getType()->getName();

            $items[] = [
                'route' => 'event_detail',
                'id' => $event->getId(),
                'title' => $title,
                'label' => $label,
                'labelKey' => null,
                'date' => $start,
                'days' => $days,
                'icon' => 'fa-regular fa-calendar',
                'badgeClass' => $this->getBadgeClass($days)
            ];
        }

        return $items;
    }

    private function getBadgeClass(int $days): string
    {
        return $days <= self::URGENT_DAYS ? 'text-bg-danger' : 'text-bg-warning';
    }

    /**
     * @return string[]
     */
    private function getUserTypes(User $user): array
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR->value => UserRoleHelper::isSupervisor($user),
            WorkUserTypeConstant::AUTHOR->value => UserRoleHelper::isAuthor($user),
            WorkUserTypeConstant::OPPONENT->value => UserRoleHelper::isOpponent($user),
            WorkUserTypeConstant::CONSULTANT->value => UserRoleHelper::isConsultant($user)
        ];

        $enabledUserTypes = array_filter($userTypes);

        return array_keys($enabledUserTypes);
    }
}
