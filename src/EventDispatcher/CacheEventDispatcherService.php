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

namespace App\EventDispatcher;

use App\EventDispatcher\GenericEvent\CacheGenericEvent;
use App\EventSubscriber\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CacheEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onClearCacheKey(string $key): void
    {
        $genericEvent = new CacheGenericEvent;
        $genericEvent->key = $key;

        $this->eventDispatcher->dispatch($genericEvent, Events::CACHE_CLEAR_KEY);
    }
}
