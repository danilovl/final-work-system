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

namespace App\Tests\Unit\Domain\User\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\CacheUserGenericEvent;
use App\Domain\User\EventDispatcher\UserCacheEventDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserCacheEventDispatcherTest extends TestCase
{
    public function testOnCreateHomepageCache(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $userCacheEventDispatcher = new UserCacheEventDispatcher($eventDispatcher);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(CacheUserGenericEvent::class),
                Events::CACHE_CREATE_HOMEPAGE
            );

        $userCacheEventDispatcher->onCreateHomepageCache(new User);
    }
}
