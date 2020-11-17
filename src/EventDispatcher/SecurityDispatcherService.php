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

use App\Entity\ResetPassword;
use App\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SecurityDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onResetPasswordTokenCreate(ResetPassword $resetPassword, int $tokenLifetime): void
    {
        $resetPasswordGenericEvent = new ResetPasswordGenericEvent;
        $resetPasswordGenericEvent->resetPassword= $resetPassword;
        $resetPasswordGenericEvent->tokenLifetime= $tokenLifetime;

        $this->eventDispatcher->dispatch($resetPasswordGenericEvent, Events::NOTIFICATION_RESET_PASSWORD_TOKEN);
    }
}
