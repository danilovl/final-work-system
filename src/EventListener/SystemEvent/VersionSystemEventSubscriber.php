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

use App\EventDispatcher\GenericEvent\VersionGenericEvent;
use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    User,
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};

class VersionSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_VERSION_CREATE => 'onVersionCreate',
            Events::SYSTEM_VERSION_EDIT => 'onVersionEdit'
        ];
    }

    private function onBaseEvent(
        VersionGenericEvent $event,
        int $systemEventTypeId,
        array $users = [true, true, true, true]
    ): void {
        $media = $event->media;
        $work = $media->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find($systemEventTypeId)
        );

        $workUsers = $work->getUsers(...$users);
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

    public function onVersionCreate(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_CREATE);
    }

    public function onVersionEdit(VersionGenericEvent $event): void
    {
        $this->onBaseEvent($event, SystemEventTypeConstant::VERSION_EDIT);
    }
}
