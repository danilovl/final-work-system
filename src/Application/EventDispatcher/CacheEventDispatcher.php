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

namespace App\Application\EventDispatcher;

use App\Application\EventDispatcher\GenericEvent\CacheClearGenericEvent;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;

readonly class CacheEventDispatcher
{
    public function __construct(private EventDispatcherService $eventDispatcher) {}

    public function onClearCacheKey(string $key): void
    {
        $genericEvent = new CacheClearGenericEvent($key);

        $this->eventDispatcher->dispatch($genericEvent, Events::CACHE_CLEAR_KEY);
    }
}
