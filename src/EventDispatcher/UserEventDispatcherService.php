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

use App\EventDispatcher\GenericEvent\UserGenericEvent;
use App\EventListener\Events;
use App\Entity\User;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class UserEventDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onUserCreate(User $user): void
    {
        $genericEvent = new GenericEvent($user);
        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_USER_CREATE);
    }

    public function onUserEdit(UserGenericEvent $userGenericEvent): void
    {
        $genericEvent = new GenericEvent($userGenericEvent);
        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_USER_EDIT);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_USER_EDIT);
    }
}
