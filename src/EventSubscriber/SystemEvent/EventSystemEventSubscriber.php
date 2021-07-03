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

namespace App\EventSubscriber\SystemEvent;

use App\EventDispatcher\GenericEvent\EventGenericEvent;
use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use App\Entity\{
    SystemEvent,
    SystemEventRecipient,
    SystemEventType
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_EVENT_CREATE => 'onEventCreate',
            Events::SYSTEM_EVENT_EDIT => 'onEventEdit',
            Events::SYSTEM_EVENT_SWITCH_SKYPE => 'onEventSwitchSkype',
            Events::SYSTEM_EVENT_COMMENT_CREATE => 'onEventCommentCreate',
            Events::SYSTEM_EVENT_COMMENT_EDIT => 'onEventCommentEdit',
            Events::SYSTEM_EVENT_RESERVATION => 'onEventReservation'
        ];
    }

    public function onEventCreate(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE)
        );

        if ($event->getParticipant()) {
            if ($event->getParticipant()->getWork()) {
                $systemEvent->setWork($event->getParticipant()->getWork());
            }

            if ($event->getParticipant()->getUser()) {
                $recipient = new SystemEventRecipient;
                $recipient->setRecipient($event->getParticipant()->getUser());
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventEdit(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_EDIT)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getParticipant()->getUser());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventSwitchSkype(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_SWITCH_SKYPE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventCommentCreate(EventGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_CREATE)
        );

        $recipient = new SystemEventRecipient;

        $recipientUser = $event->getParticipant()->getUser();
        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $recipientUser = $event->getOwner();
        }

        $recipient->setRecipient($recipientUser);
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventCommentEdit(EventGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_EDIT)
        );

        $recipient = new SystemEventRecipient;

        $recipientUser = $event->getParticipant()->getUser();
        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $recipientUser = $event->getOwner();
        }

        $recipient->setRecipient($recipientUser);
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventReservation(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->entityManagerService->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
