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
use App\Domain\Event\EventDispatcher\EventEventDispatcherService;
use App\Domain\Event\EventDispatcher\GenericEvent\EventGenericEvent;
use App\Domain\EventParticipant\Entity\EventParticipant;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

class EventEventDispatcherServiceTest extends TestCase
{
    private EventDispatcherInterface $eventDispatcher;

    private AsyncService $asyncService;

    private EventEventDispatcherService $eventEventDispatcherService;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->asyncService = new AsyncService;
        $this->eventEventDispatcherService = new EventEventDispatcherService($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('dispatchProvider')]
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
            ->will($this->createReturnCallback($expectEvents, $expectNames));;

        $this->eventEventDispatcherService->{$method}($event);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): ReturnCallback
    {
        return $this->returnCallback(function (EventGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): EventGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        });
    }

    public static function dispatchProvider(): Generator
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
