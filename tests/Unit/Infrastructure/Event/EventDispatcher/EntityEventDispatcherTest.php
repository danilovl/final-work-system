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

namespace App\Tests\Unit\Infrastructure\Event\EventDispatcher;

use App\Infrastructure\Event\EventDispatcher\EntityEventDispatcher;
use App\Infrastructure\Service\EventDispatcherService;
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\Event\InfrastructureEvents;

class EntityEventDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherService $eventDispatcher;

    private AsyncService $asyncService;

    private EntityEventDispatcher $entityEventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherService::class);

        $stubEventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->asyncService = new AsyncService($stubEventDispatcher);

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

        $this->assertSame(InfrastructureEvents::ENTITY_CREATE, $dispatchedEvents[0][1]);
        $this->assertSame(InfrastructureEvents::ENTITY_CREATE_ASYNC, $dispatchedEvents[1][1]);
    }
}
