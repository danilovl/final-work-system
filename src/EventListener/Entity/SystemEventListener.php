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

namespace App\EventListener\Entity;

use App\Constant\CacheKeyConstant;
use App\Entity\SystemEvent;
use App\EventDispatcher\CacheEventDispatcherService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class SystemEventListener
{
    public function __construct(private CacheEventDispatcherService $cacheEventDispatcherService)
    {
    }

    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof SystemEvent) {
            return;
        }

        $this->clearCache($entity);
    }

    public function clearCache(SystemEvent $systemEvent): void
    {
        foreach ($systemEvent->getRecipient() as $recipient) {
            $this->cacheEventDispatcherService->onClearCacheKey(
                sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $recipient->getRecipient()->getId())
            );
        }
    }
}