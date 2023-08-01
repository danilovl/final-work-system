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

use App\Application\EventDispatcher\CacheEventDispatcherService;
use App\Application\EventDispatcher\GenericEvent\{
    CacheUserGenericEvent,
    CacheClearGenericEvent
};
use App\Application\EventSubscriber\Events;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CacheEventDispatcherServiceTest extends TestCase
{
    public function testOnClearCacheKey(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $cacheEventDispatcherService = new CacheEventDispatcherService($eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(CacheClearGenericEvent::class),
                Events::CACHE_CLEAR_KEY
            );

        $cacheEventDispatcherService->onClearCacheKey('test_key');
    }

    public function testOnCreateHomepageCache(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $cacheEventDispatcherService = new CacheEventDispatcherService($eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(CacheUserGenericEvent::class),
                Events::CACHE_CREATE_HOMEPAGE
            );

        $cacheEventDispatcherService->onCreateHomepageCache(new User);
    }
}
