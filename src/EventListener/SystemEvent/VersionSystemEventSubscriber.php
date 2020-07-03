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

use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    User,
    Work,
    Media,
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};
use Symfony\Component\EventDispatcher\GenericEvent;

class VersionSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_VERSION_CREATE => 'onVersionCreate',
            Events::SYSTEM_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    public function onVersionCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var Work $work */
        $work = $media->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::VERSION_CREATE)
        );

        $workUsers = $work->getUsers(true, true, true, true);
        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $recipient = new SystemEventRecipient;
                $recipient->setRecipient($user);
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persistAndFlush($systemEvent);
    }

    public function onVersionEdit(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var Work $work */
        $work = $media->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::VERSION_EDIT)
        );

        $workUsers = $work->getUsers(true, true, true, true);
        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $recipient = new SystemEventRecipient;
                $recipient->setRecipient($user);
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persistAndFlush($systemEvent);
    }
}
