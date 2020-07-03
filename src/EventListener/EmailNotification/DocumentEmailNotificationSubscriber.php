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

use App\Constant\WorkUserTypeConstant;
use App\Entity\{
    User,
    Media
};
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class DocumentEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        $owner = $media->getOwner();

        $subject = $this->trans('subject.document_create');
        $body = $this->twig->render($this->getTemplate('document_create'), [
            'media' => $media
        ]);
        $recipientArray = $owner->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $this->addEmailNotificationToQueue($subject, $user->getEmail(), $this->sender, $body);
        }
    }
}