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

namespace App\Tests\Unit\Domain\Work\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;
use App\Infrastructure\Service\EventDispatcherService;
use Closure;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WorkEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private WorkEventDispatcher $workEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->workEventDispatcher = new WorkEventDispatcher($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('provideDispatchCases')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $work = $this->createStub(Work::class);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->willReturnCallback($this->createReturnCallback($expectEvents, $expectNames));

        $this->workEventDispatcher->{$method}($work);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): Closure
    {
        return function (WorkGenericEvent|UserGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): WorkGenericEvent|UserGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        };
    }

    public static function provideDispatchCases(): Generator
    {
        yield ['onWorkCreate', 1,
            [
                WorkGenericEvent::class,
                UserGenericEvent::class
            ],
            [
                Events::WORK_CREATE
            ]
        ];

        yield ['onWorkEdit', 1,
            [
                WorkGenericEvent::class,
                UserGenericEvent::class
            ],
            [
                Events::WORK_EDIT
            ]
        ];

        yield ['onWorkEditAuthor', 2,
            [
                UserGenericEvent::class,
                WorkGenericEvent::class
            ],
            [
                Events::USER_EDIT,
                Events::WORK_AUTHOR_EDIT
            ]
        ];
    }
}
