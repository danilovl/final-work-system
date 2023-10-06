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

use App\Application\Cache\HomepageCache;
use App\Application\EventDispatcher\GenericEvent\{
    CacheUserGenericEvent,
    CacheClearGenericEvent
};
use App\Application\EventSubscriber\CacheEventSubscriber;
use App\Application\EventSubscriber\Events;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class CacheEventSubscriberTest extends TestCase
{
    private CacheInterface $cache;
    private HomepageCache $homepageCache;
    private CacheEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->homepageCache = $this->createMock(HomepageCache::class);

        $this->subscriber = new CacheEventSubscriber($this->cache, $this->homepageCache);
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

    public function testOnCreateHomepageCache(): void
    {
        $event = new CacheUserGenericEvent(new User);

        $this->homepageCache
            ->expects($this->once())
            ->method('createHomepagePaginator');

        $this->subscriber->onCreateHomepageCache($event);

        $this->assertTrue(true);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subscriber::getSubscribedEvents();

        $this->assertEquals('onClearCacheKey', $subscribedEvents[Events::CACHE_CLEAR_KEY]);
        $this->assertEquals('onCreateHomepageCache', $subscribedEvents[Events::CACHE_CREATE_HOMEPAGE]);
    }
}
