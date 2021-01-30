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

namespace App\EventSubscriber\EmailNotification;

use App\EventDispatcher\GenericEvent\UserGenericEvent;
use App\EventSubscriber\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_USER_CREATE => 'onUserCreate',
            Events::NOTIFICATION_USER_EDIT => 'onUserEdit'
        ];
    }

    public function onUserCreate(UserGenericEvent $event): void
    {
        $user = $event->user;

        $subject = $this->trans('subject.user_create');
        $to = $user->getEmail();
        $body = $this->twig->render($this->getTemplate('user_create'), [
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword()
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onUserEdit(UserGenericEvent $event): void
    {
        $user = $event->user;

        $subject = $this->trans('subject.user_edit');
        $to = $user->getEmail();
        $body = $this->twig->render($this->getTemplate('user_edit'));

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }
}