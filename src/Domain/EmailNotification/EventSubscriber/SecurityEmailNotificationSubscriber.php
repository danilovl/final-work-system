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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\ResetPassword\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SecurityEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SECURITY_RESET_PASSWORD_TOKEN => 'onResetPasswordToken'
        ];
    }

    public function onResetPasswordToken(ResetPasswordGenericEvent $genericEvent): void
    {
        $resetPassword = $genericEvent->resetPassword;
        $toUser = $resetPassword->getUser();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.event_reservation',
            'to' => $toUser->getEmail(),
            'from' => $this->sender,
            'template' => 'reset_password_token_create',
            'templateParameters' => [
                'hashedToken' => $resetPassword->getHashedToken(),
                'tokenLifetime' => $genericEvent->tokenLifetime
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }
}
