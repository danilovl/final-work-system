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

namespace App\Tests\Unit\Domain\SystemEventRecipient\EventListener\Entity;

use App\Application\Constant\CacheKeyConstant;
use App\Application\EventDispatcher\CacheEventDispatcherService;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventRecipient\EventListener\Entity\SystemEventRecipientListener;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

class SystemEventRecipientListenerTest extends TestCase
{
    public function testClearCache(): void
    {
        $user = new User;
        $user->setId(1);

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($user);

        $cacheEventDispatcherService = $this->createMock(CacheEventDispatcherService::class);
        $cacheEventDispatcherService
            ->expects($this->once())
            ->method('onClearCacheKey')
            ->with(sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $recipient->getRecipient()->getId()));

        $cacheEventDispatcherService
            ->expects($this->once())
            ->method('onCreateHomepageCache')
            ->with($recipient->getRecipient());

        $listener = new SystemEventRecipientListener($cacheEventDispatcherService);
        $listener->clearCache($recipient);
    }
}
