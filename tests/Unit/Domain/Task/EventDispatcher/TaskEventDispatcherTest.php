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

namespace App\Tests\Unit\Domain\Task\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Task\Constant\TaskStatusConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use App\Domain\Task\EventDispatcher\TaskEventDispatcher;
use App\Infrastructure\Service\EventDispatcherService;
use Closure;
use Danilovl\AsyncBundle\Service\AsyncService;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private TaskEventDispatcher $taskEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->taskEventDispatcher = new TaskEventDispatcher(
            eventDispatcher: $this->eventDispatcher,
            asyncService: $this->asyncService
        );
    }

    #[DataProvider('provideDispatchCases')]
    public function testDispatch(
        string $method,
        int $exactly,
        array $otherArguments,
        array $expectEvents,
        array $expectNames
    ): void {
        $task = $this->createStub(Task::class);
        $task
            ->method('isActive')
            ->willReturn(true);

        $task
            ->method('getSystemEvents')
            ->willReturn(new ArrayCollection([]));

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->willReturnCallback($this->createReturnCallback($expectEvents, $expectNames));

        $arguments = array_merge([$task], $otherArguments);

        $this->taskEventDispatcher->{$method}(...$arguments);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): Closure
    {
        return function (TaskGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): TaskGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        };
    }

    public static function provideDispatchCases(): Generator
    {
        yield ['onTaskCreate', 1, [],
            [
                TaskGenericEvent::class
            ],
            [
                Events::TASK_CREATE
            ]
        ];

        yield ['onTaskEdit', 1, [],
            [
                TaskGenericEvent::class
            ],
            [
                Events::TASK_CREATE
            ]
        ];

        yield ['onTaskNotifyComplete', 1, [],
            [
                TaskGenericEvent::class
            ],
            [
                Events::TASK_NOTIFY_COMPLETE
            ]
        ];

        yield ['onTaskChangeStatus', 1,
            [
                TaskStatusConstant::COMPLETE->value
            ],
            [
                TaskGenericEvent::class
            ],
            [
                Events::TASK_INCOMPLETE
            ]
        ];

        yield ['onTaskReminderCreate', 1, [],
            [
                TaskGenericEvent::class
            ],
            [
                Events::TASK_REMIND_DEADLINE_CREATE
            ]
        ];
    }
}
