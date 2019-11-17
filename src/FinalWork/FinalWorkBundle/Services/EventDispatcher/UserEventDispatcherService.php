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

namespace FinalWork\FinalWorkBundle\Services\EventDispatcher;

use FinalWork\FinalWorkBundle\EventListener\Events;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class UserEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * UserEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param User $user
     */
    public function onUserCreate(User $user): void
    {
        $event = new GenericEvent($user);
        $this->eventDispatcher->dispatch(Events::NOTIFICATION_USER_CREATE, $event);
     }

    /**
     * @param User $user
     */
    public function onUserEdit(User $user): void
    {
        $event = new GenericEvent($user);
        $this->eventDispatcher->dispatch(Events::NOTIFICATION_USER_EDIT, $event);
        $this->eventDispatcher->dispatch(Events::SYSTEM_USER_EDIT, $event);
    }
}
