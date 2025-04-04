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

namespace App\Tests\Unit\Application\EventSubscriber;

use App\Application\EventDispatcher\GenericEvent\CacheClearGenericEvent;
use App\Application\EventSubscriber\{
    Events,
    CacheEventSubscriber
};
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class CacheEventSubscriberTest extends TestCase
{
    private MockObject&CacheInterface $cache;

    private CacheEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);

        $this->subscriber = new CacheEventSubscriber($this->cache);
    }

    public function testOnClearCacheKey(): void
    {
        $event = new CacheClearGenericEvent('key');

        $this->cache
            ->expects($this->once())
            ->method('delete');

        $this->subscriber->onClearCacheKey($event);

        $this->assertTrue(true);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subscriber::getSubscribedEvents();

        $this->assertEquals('onClearCacheKey', $subscribedEvents[Events::CACHE_CLEAR_KEY]);
    }
}
