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

namespace App\Domain\User\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\GenericEvent\CacheUserGenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class UserCacheEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher) {}

    public function onCreateHomepageCache(User $user): void
    {
        $genericEvent = new CacheUserGenericEvent($user);

        $this->eventDispatcher->dispatch($genericEvent, Events::CACHE_CREATE_HOMEPAGE);
    }
}
