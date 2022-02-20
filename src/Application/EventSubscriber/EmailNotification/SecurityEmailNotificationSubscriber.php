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

namespace App\Application\EventSubscriber\EmailNotification;

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\Application\EventSubscriber\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SecurityEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_RESET_PASSWORD_TOKEN => 'onResetPasswordToken'
        ];
    }

    public function onResetPasswordToken(ResetPasswordGenericEvent $genericEvent): void
    {
        $resetPassword = $genericEvent->resetPassword;

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_reservation'),
            'to' => $resetPassword->getUser()->getEmail(),
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