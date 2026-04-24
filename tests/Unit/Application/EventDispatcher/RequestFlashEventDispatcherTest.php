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

namespace App\Tests\Unit\Application\EventDispatcher;

use App\Application\EventDispatcher\RequestFlashEventDispatcher;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class RequestFlashEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private RequestFlashEventDispatcher $requestFlashEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->requestFlashEventDispatcher = new RequestFlashEventDispatcher($this->eventDispatcher);
    }

    public function testOnCreateFailure(): void
    {
        $this->assertEventDispatched(
            fn () => $this->requestFlashEventDispatcher->onCreateFailure(),
            Events::REQUEST_FLASH_CREATE_FAILURE
        );
    }

    public function testOnSaveFailure(): void
    {
        $this->assertEventDispatched(
            fn () => $this->requestFlashEventDispatcher->onSaveFailure(),
            Events::REQUEST_FLASH_SAVE_FAILURE
        );
    }

    public function testOnRemoveFailure(): void
    {
        $this->assertEventDispatched(
            fn () => $this->requestFlashEventDispatcher->onRemoveFailure(),
            Events::REQUEST_FLASH_DELETE_FAILURE
        );
    }

    private function assertEventDispatched(callable $methodCall, string $expectedEventName): void
    {
        $dispatchedEvents = [];

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(static function (object $event, string $eventName) use (&$dispatchedEvents): object {
                $dispatchedEvents[] = [$event, $eventName];

                return $event;
            });

        $methodCall();

        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(stdClass::class, $dispatchedEvents[0][0]);
        $this->assertSame($expectedEventName, $dispatchedEvents[0][1]);
    }
}
