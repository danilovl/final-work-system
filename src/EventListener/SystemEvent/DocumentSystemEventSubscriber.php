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

namespace App\EventListener\SystemEvent;

use App\Constant\WorkUserTypeConstant;
use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    User,
    Media,
    SystemEvent,
    SystemEventRecipient,
    SystemEventType
};
use Symfony\Component\EventDispatcher\GenericEvent;

class DocumentSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_DOCUMENT_CREATE => 'onDocumentCreate'
        ];
    }

    public function onDocumentCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var User $owner */
        $owner = $media->getOwner();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($owner);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::DOCUMENT_CREATE)
        );

        $recipientArray = $owner->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $recipientAuthor = new SystemEventRecipient;
            $recipientAuthor->setRecipient($user);
            $systemEvent->addRecipient($recipientAuthor);
        }

        $this->em->persistAndFlush($systemEvent);
    }
}
