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

use App\Entity\{
    User,
    Media
};
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class VersionEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_VERSION_CREATE => 'onVersionCreate',
            Events::NOTIFICATION_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    public function onVersionCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        $work = $media->getWork();

        $subject = $this->trans('subject.version_create');
        $body = $this->twig->render($this->getTemplate('work_version_create'), [
            'media' => $media,
            'work' => $work
        ]);

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $to = $user->getEmail();
                $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
            }
        }
    }

    public function onVersionEdit(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        $work = $media->getWork();

        $subject = $this->trans('subject.version_edit');
        $body = $this->twig->render($this->getTemplate('work_version_edit'), [
            'media' => $media,
            'work' => $work
        ]);

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $to = $user->getEmail();
                $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
            }
        }
    }
}