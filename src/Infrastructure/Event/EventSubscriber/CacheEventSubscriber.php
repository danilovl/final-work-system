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

namespace App\Infrastructure\Event\EventSubscriber;

use App\Application\EventDispatcher\GenericEvent\CacheClearGenericEvent;
use App\Application\EventSubscriber\Events;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class CacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::CACHE_CLEAR_KEY => 'onClearCacheKey'
        ];
    }

    public function onClearCacheKey(CacheClearGenericEvent $event): void
    {
        $this->cache->delete($event->key);
    }
}
