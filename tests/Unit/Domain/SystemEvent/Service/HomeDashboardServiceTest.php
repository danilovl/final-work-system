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

namespace App\Tests\Unit\Domain\SystemEvent\Service;

use App\Application\Interfaces\Bus\{
    QueryBusInterface,
    QueryInterface
};
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
use App\Domain\Event\Entity\Event;
use App\Domain\EventType\Entity\EventType;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use App\Domain\SystemEvent\Service\HomeDashboardService;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Bus\Query\UserWorks\{
    GetUserWorksQuery,
    GetUserWorksQueryResult
};
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Bus\Query\WorkStatus\{
    GetWorkStatusQuery,
    GetWorkStatusQueryResult
};
use App\Domain\WorkStatus\Entity\WorkStatus;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use PHPUnit\Framework\TestCase;

class HomeDashboardServiceTest extends TestCase
{
    /** @var Work[] */
    private array $works = [];

    /** @var Event[] */
    private array $ownerEvents = [];

    /** @var Event[] */
    private array $participantEvents = [];

    private HomeDashboardService $homeDashboardService;

    protected function setUp(): void
    {
        $queryBus = $this->createStub(QueryBusInterface::class);
        $queryBus
            ->method('handle')
            ->willReturnCallback(fn (QueryInterface $query): object => match (true) {
                $query instanceof GetWorkStatusQuery => new GetWorkStatusQueryResult(new WorkStatus),
                $query instanceof GetUserWorksQuery => new GetUserWorksQueryResult(new ArrayCollection($this->works)),
                $query instanceof GetEventsByOwnerQuery => new GetEventsByOwnerQueryResult($this->ownerEvents),
                $query instanceof GetEventsByParticipantQuery => new GetEventsByParticipantQueryResult($this->participantEvents),
                $query instanceof GetTotalUnreadMessagesByUserQuery => new GetTotalUnreadMessagesByUserQueryResult(4),
                default => throw new LogicException('Unexpected query')
            });

        $systemEventFacade = $this->createStub(SystemEventFacade::class);
        $systemEventFacade
            ->method('getTotalUnreadSystemEventsByRecipient')
            ->willReturn(2);

        $this->homeDashboardService = new HomeDashboardService($queryBus, $systemEventFacade);
    }

    public function testCreateCountsAndUnread(): void
    {
        $this->works = [
            $this->createWork(1, '+5 days'),
            $this->createWork(2, '+40 days'),
            $this->createWork(3, '-2 days')
        ];

        $dashboard = $this->homeDashboardService->create($this->createSupervisor());

        $this->assertSame(3, $dashboard['activeWorksCount']);
        $this->assertSame('supervisor', $dashboard['activeWorksType']);
        $this->assertSame(4, $dashboard['unreadMessagesCount']);
        $this->assertSame(2, $dashboard['unreadAlertsCount']);
        $this->assertSame(1, $dashboard['upcomingCount']);

        $item = $dashboard['upcoming'][0];

        $this->assertSame('work_detail', $item['route']);
        $this->assertSame(1, $item['id']);
        $this->assertSame('app.text.work_deadline', $item['labelKey']);
        $this->assertSame('text-bg-warning', $item['badgeClass']);
    }

    public function testCreateMergesEventsAndSortsByDate(): void
    {
        $this->works = [$this->createWork(1, '+5 days')];

        $sharedEvent = $this->createEvent(10, '+2 days 09:00', 'Consultation');
        $this->ownerEvents = [
            $sharedEvent,
            $this->createEvent(11, '+1 day 08:00', 'Defense')
        ];
        $this->participantEvents = [
            $sharedEvent,
            $this->createEvent(12, '+10 days 10:00', 'Meeting')
        ];

        $dashboard = $this->homeDashboardService->create($this->createSupervisor());
        $upcoming = $dashboard['upcoming'];
        $ids = array_column($upcoming, 'id');
        $days = array_column($upcoming, 'days');
        $badgeClasses = array_column($upcoming, 'badgeClass');

        $this->assertSame(4, $dashboard['upcomingCount']);
        $this->assertSame([11, 10, 1, 12], $ids);
        $this->assertSame(1, $days[0]);
        $this->assertSame(2, $days[1]);
        $this->assertSame(10, $days[3]);
        $this->assertSame(['text-bg-danger', 'text-bg-danger', 'text-bg-warning', 'text-bg-warning'], $badgeClasses);
        $this->assertSame('event_detail', $upcoming[0]['route']);
        $this->assertSame('Defense', $upcoming[0]['title']);
    }

    public function testCreateLimitsUpcomingList(): void
    {
        $events = [];
        for ($id = 1; $id <= 10; $id++) {
            $events[] = $this->createEvent($id, "+{$id} days 09:00", 'Consultation');
        }

        $this->ownerEvents = $events;

        $dashboard = $this->homeDashboardService->create($this->createSupervisor());
        $upcomingItems = $dashboard['upcoming'];

        $this->assertSame(10, $dashboard['upcomingCount']);
        $this->assertCount(8, $upcomingItems);
    }

    public function testCreateWithoutWorkRoles(): void
    {
        $user = new User;
        $user->setId(1);
        $user->setRoles([UserRoleConstant::ADMIN->value]);

        $dashboard = $this->homeDashboardService->create($user);

        $this->assertSame(0, $dashboard['activeWorksCount']);
        $this->assertNull($dashboard['activeWorksType']);
    }

    private function createSupervisor(): User
    {
        $user = new User;
        $user->setId(1);
        $user->setRoles([UserRoleConstant::SUPERVISOR->value]);

        return $user;
    }

    private function createWork(int $id, string $deadline): Work
    {
        $work = new Work;
        $work->setId($id);
        $work->setTitle('Work ' . $id);
        $work->setDeadline(new DateTimeImmutable($deadline));

        return $work;
    }

    private function createEvent(int $id, string $start, string $typeName): Event
    {
        $eventType = new EventType;
        $eventType->setName($typeName);

        $startDate = new DateTimeImmutable('today')->modify($start);

        $event = new Event;
        $event->setId($id);
        $event->setName($typeName);
        $event->setType($eventType);
        $event->setStart($startDate);
        $event->setEnd($startDate->modify('+1 hour'));

        return $event;
    }
}
