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

namespace App\Tests\Unit\Infrastructure\Event\EventDispatcher;

use App\Infrastructure\Event\EventDispatcher\CacheEventDispatcher;
use App\Infrastructure\Event\EventDispatcher\GenericEvent\CacheClearGenericEvent;
use App\Infrastructure\Service\EventDispatcherService;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\Event\InfrastructureEvents;

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
                InfrastructureEvents::CACHE_CLEAR_KEY
            );

        $cacheEventDispatcher->onClearCacheKey('test_key');
    }
}
