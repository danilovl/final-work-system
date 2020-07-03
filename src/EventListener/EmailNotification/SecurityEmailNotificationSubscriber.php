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

namespace App\EventListener\EmailNotification;

use App\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class SecurityEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_RESET_PASSWORD_TOKEN => 'onResetPasswordToken'
        ];
    }

    public function onResetPasswordToken(GenericEvent $genericEvent): void
    {
        /** @var ResetPasswordGenericEvent $resetPasswordGenericEvent */
        $resetPasswordGenericEvent = $genericEvent->getSubject();
        $resetPassword = $resetPasswordGenericEvent->resetPassword;

        $subject = $this->trans('subject.event_reservation');
        $to = $resetPassword->getUser()->getEmail();
        $body = $this->twig->render($this->getTemplate('reset_password_token_create'), [
            'resetPassword' => $resetPassword,
            'tokenLifetime' => $resetPasswordGenericEvent->tokenLifetime
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }
}