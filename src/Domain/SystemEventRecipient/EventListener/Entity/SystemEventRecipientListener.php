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

namespace App\Domain\SystemEventRecipient\EventListener\Entity;

use App\Application\Constant\CacheKeyConstant;
use App\Application\EventDispatcher\CacheEventDispatcherService;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use Doctrine\ORM\Event\LifecycleEventArgs;

class SystemEventRecipientListener
{
    public function __construct(private CacheEventDispatcherService $cacheEventDispatcherService)
    {
    }

    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof SystemEventRecipient) {
            return;
        }

        $this->clearCache($entity);
    }

    public function clearCache(SystemEventRecipient $recipient): void
    {
        $this->cacheEventDispatcherService->onClearCacheKey(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $recipient->getRecipient()->getId())
        );

        $this->cacheEventDispatcherService->onCreateHomepageCache($recipient->getRecipient());
    }
}