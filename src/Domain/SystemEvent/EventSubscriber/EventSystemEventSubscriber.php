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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\Event\EventDispatcher\GenericEvent\{
    EventCommentGenericEvent,
    EventGenericEvent};
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use App\Domain\SystemEventType\Entity\SystemEventType;
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
        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($type);

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
        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_EDIT->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($type);

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getParticipant()->getUser());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventSwitchSkype(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;
        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_SWITCH_SKYPE->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($type);

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventCommentCreate(EventCommentGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_CREATE->value);

        $systemEvent->setType($type);

        $recipient = new SystemEventRecipient;

        $recipientUser = $event->getParticipant()->getUser();
        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $recipientUser = $event->getOwner();
        }

        $recipient->setRecipient($recipientUser);
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onEventCommentEdit(EventCommentGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_EDIT->value);

        $systemEvent->setType($type);

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
        $type = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE->value);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($type);

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }
}
