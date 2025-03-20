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

namespace App\Tests\Unit\Domain\SystemEvent\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\SystemEvent\Cache\HomepageCache;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\CacheUserGenericEvent;
use App\Domain\User\EventSubscriber\CacheEventSubscriber;
use PHPUnit\Framework\TestCase;

class CacheEventSubscriberTest extends TestCase
{
    private HomepageCache $homepageCache;

    private CacheEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->homepageCache = $this->createMock(HomepageCache::class);

        $this->subscriber = new CacheEventSubscriber($this->homepageCache);
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

        $this->assertEquals('onCreateHomepageCache', $subscribedEvents[Events::CACHE_CREATE_HOMEPAGE]);
    }
}
