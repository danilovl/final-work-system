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

use App\Application\EventDispatcher\EntityEventDispatcher;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private EntityEventDispatcher $entityEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);
        $this->asyncService = new AsyncService;
        $this->entityEventDispatcher = new EntityEventDispatcher($this->eventDispatcher, $this->asyncService);
    }

    public function testOnResetPasswordTokenCreate(): void
    {
        $dispatchedEvents = [];

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(static function (object $event, string $eventName) use (&$dispatchedEvents): object {
                $dispatchedEvents[] = [$event, $eventName];

                return $event;
            });

        $this->entityEventDispatcher->onCreate(new class ( ) {});
        $this->asyncService->call();

        $this->assertCount(2, $dispatchedEvents);

        $this->assertSame(Events::ENTITY_CREATE, $dispatchedEvents[0][1]);
        $this->assertSame(Events::ENTITY_CREATE_ASYNC, $dispatchedEvents[1][1]);
    }
}
