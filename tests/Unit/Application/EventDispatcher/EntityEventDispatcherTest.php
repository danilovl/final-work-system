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
use App\Application\EventDispatcher\GenericEvent\EntityPostFlushGenericEvent;
use App\Application\EventSubscriber\Events;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Application\Service\EventDispatcherService;

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
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(EntityPostFlushGenericEvent::class),
                Events::ENTITY_POST_PERSIST_FLUSH
            );

        $this->entityEventDispatcher->onPostPersistFlush(new class ( ) {});
        $this->asyncService->call();
    }
}
