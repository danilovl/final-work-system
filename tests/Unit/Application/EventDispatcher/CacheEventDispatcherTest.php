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

use App\Application\EventDispatcher\CacheEventDispatcher;
use App\Application\EventDispatcher\GenericEvent\CacheClearGenericEvent;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;
use PHPUnit\Framework\TestCase;

class CacheEventDispatcherTest extends TestCase
{
    public function testOnClearCacheKey(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherService::class);
        $cacheEventDispatcher = new CacheEventDispatcher($eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(CacheClearGenericEvent::class),
                Events::CACHE_CLEAR_KEY
            );

        $cacheEventDispatcher->onClearCacheKey('test_key');
    }
}
