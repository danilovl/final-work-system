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
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserEventDispatcherService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncService $asyncService
    ) {}

    public function onUserCreate(User $user): void
    {
        $genericEvent = new UserGenericEvent;
        $genericEvent->user = $user;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_USER_CREATE);
        });
    }

    public function onUserEdit(User $user, User $owner): void
    {
        $genericEvent = new UserGenericEvent;
        $genericEvent->user = $user;
        $genericEvent->owner = $owner;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_USER_EDIT);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_USER_EDIT);
        });
    }
}
