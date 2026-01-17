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

namespace App\Domain\ResetPassword\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\Infrastructure\Service\EventDispatcherService;

readonly class SecurityDispatcher
{
    public function __construct(private EventDispatcherService $eventDispatcher) {}

    public function onResetPasswordTokenCreate(ResetPassword $resetPassword, int $tokenLifetime): void
    {
        $resetPasswordGenericEvent = new ResetPasswordGenericEvent($resetPassword, $tokenLifetime);

        $this->eventDispatcher->dispatch($resetPasswordGenericEvent, Events::SECURITY_RESET_PASSWORD_TOKEN);
    }
}
