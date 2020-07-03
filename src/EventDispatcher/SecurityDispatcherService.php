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

use App\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class SecurityDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onResetPasswordTokenCreate(ResetPasswordGenericEvent $resetPasswordGenericEvent): void
    {
        $genericEvent = new GenericEvent($resetPasswordGenericEvent);
        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_RESET_PASSWORD_TOKEN);
    }
}
