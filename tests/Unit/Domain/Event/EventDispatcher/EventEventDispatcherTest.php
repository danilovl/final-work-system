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

namespace App\Tests\Unit\Domain\Event\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\EventEventDispatcher;
use App\Domain\Event\EventDispatcher\GenericEvent\EventGenericEvent;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Infrastructure\Service\EventDispatcherService;
use Closure;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private EventEventDispatcher $eventEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->eventEventDispatcher = new EventEventDispatcher($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('provideDispatchCases')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $event = $this->createMock(Event::class);
        $event
            ->expects($this->any())
            ->method('getParticipant')
            ->willReturn(new EventParticipant);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->willReturnCallback($this->createReturnCallback($expectEvents, $expectNames));

        $this->eventEventDispatcher->{$method}($event);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): Closure
    {
        return function (EventGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): EventGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        };
    }

    public static function provideDispatchCases(): Generator
    {
        yield ['onEventEdit', 1,
            [
                EventGenericEvent::class
            ],
            [
                Events::EVENT_EDIT
            ]
        ];

        yield ['onEventSwitchToSkype', 1,
            [
                EventGenericEvent::class
            ],
            [
                Events::EVENT_SWITCH_SKYPE
            ]
        ];

        yield ['onEventCalendarReservation', 1,
            [
                EventGenericEvent::class
            ],
            [
                Events::EVENT_RESERVATION
            ]
        ];

        yield ['onEventCalendarEdit', 1,

            [
                EventGenericEvent::class
            ],
            [
                Events::EVENT_EDIT
            ]
        ];
    }
}
