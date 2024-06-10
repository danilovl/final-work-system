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
use App\Domain\Work\EventDispatcher\WorkEventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

class WorkEventDispatcherServiceTest extends TestCase
{
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly AsyncService $asyncService;
    private readonly WorkEventDispatcherService $workEventDispatcherService;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->asyncService = new AsyncService;
        $this->workEventDispatcherService = new WorkEventDispatcherService($this->eventDispatcher, $this->asyncService);
    }

    #[DataProvider('dispatchProvider')]
    public function testDispatch(string $method, int $exactly, array $expectEvents, array $expectNames): void
    {
        $work = $this->createMock(Work::class);

        $this->eventDispatcher
            ->expects($this->exactly($exactly))
            ->method('dispatch')
            ->will($this->createReturnCallback($expectEvents, $expectNames));

        $this->workEventDispatcherService->{$method}($work);
        $this->asyncService->call();
    }

    private function createReturnCallback(array $expectEvents, array $expectNames): ReturnCallback
    {
        return $this->returnCallback(function (WorkGenericEvent|UserGenericEvent $event, string $eventName) use ($expectEvents, $expectNames): WorkGenericEvent|UserGenericEvent {
            $this->assertTrue(in_array(get_class($event), $expectEvents, true));
            $this->assertTrue(in_array($eventName, $expectNames, true));

            return $event;
        });
    }

    public static function dispatchProvider(): Generator
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
