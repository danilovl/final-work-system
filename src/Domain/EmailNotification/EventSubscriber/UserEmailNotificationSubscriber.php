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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::USER_CREATE => 'onUserCreate',
            Events::USER_EDIT => 'onUserEdit'
        ];
    }

    public function onUserCreate(UserGenericEvent $event): void
    {
        $user = $event->user;

        $emailNotificationToQueueData = new EmailNotificationMessage(
            locale: $user->getLocale() ?? $this->locale,
            subject: 'subject.user_create',
            to: $user->getEmail(),
            from: $this->sender,
            template: 'user_create',
            templateParameters: [
                'username' => $user->getUsername(),
                'password' => $user->getPlainPassword()
            ]
        );

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onUserEdit(UserGenericEvent $event): void
    {
        $user = $event->user;

        $emailNotificationToQueueData = new EmailNotificationMessage(
            locale: $this->locale,
            subject: 'subject.user_edit',
            to: $user->getEmail(),
            from: $this->sender,
            template: 'user_edit'
        );

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }
}
