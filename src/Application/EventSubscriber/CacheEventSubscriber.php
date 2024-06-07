<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\EventSubscriber;

use App\Application\Cache\HomepageCache;
use App\Application\EventDispatcher\GenericEvent\{
    CacheUserGenericEvent,
    CacheClearGenericEvent
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class CacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CacheInterface $cache,
        private HomepageCache $homepageCache
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CACHE_CLEAR_KEY => 'onClearCacheKey',
            Events::CACHE_CREATE_HOMEPAGE => 'onCreateHomepageCache'
        ];
    }

    public function onClearCacheKey(CacheClearGenericEvent $event): void
    {
        $this->cache->delete($event->key);
    }

    public function onCreateHomepageCache(CacheUserGenericEvent $event): void
    {
        $this->homepageCache->createHomepagePaginator($event->user);
    }
}
