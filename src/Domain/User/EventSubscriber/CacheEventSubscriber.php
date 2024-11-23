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

namespace App\Domain\User\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\SystemEvent\Cache\HomepageCache;
use App\Domain\User\EventDispatcher\GenericEvent\CacheUserGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class CacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private HomepageCache $homepageCache) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CACHE_CREATE_HOMEPAGE => 'onCreateHomepageCache'
        ];
    }

    public function onCreateHomepageCache(CacheUserGenericEvent $event): void
    {
        $this->homepageCache->createHomepagePaginator($event->user);
    }
}
