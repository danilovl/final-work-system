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

namespace Application\EventDispatcher;

use App\Application\EventDispatcher\EntityEventDispatcherService;
use App\Application\EventDispatcher\GenericEvent\EntityPostFlushGenericEvent;
use App\Application\EventSubscriber\Events;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityEventDispatcherServiceTest extends TestCase
{
    private readonly EventDispatcherInterface $eventDispatcher;
    private readonly AsyncService $asyncService;
    private readonly EntityEventDispatcherService $entityEventDispatcherService;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->asyncService = new AsyncService;
        $this->entityEventDispatcherService = new EntityEventDispatcherService($this->eventDispatcher, $this->asyncService);
    }

    public function testOnResetPasswordTokenCreate(): void
    {
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(EntityPostFlushGenericEvent::class),
                Events::ENTITY_POST_PERSIST_FLUSH
            );

        $this->entityEventDispatcherService->onPostPersistFlush(new class {});
        $this->asyncService->call();
    }
}
