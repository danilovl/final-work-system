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

use App\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\EventDispatcher\GenericEvent\EventGenericEvent;
use App\EventSubscriber\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_EVENT_CREATE => 'onEventCreate',
            Events::NOTIFICATION_EVENT_EDIT => 'onEventEdit',
            Events::NOTIFICATION_EVENT_SWITCH_SKYPE => 'onEventSwitchSkype',
            Events::NOTIFICATION_EVENT_COMMENT_CREATE => 'onEventCommentCreate',
            Events::NOTIFICATION_EVENT_COMMENT_EDIT => 'onEventCommentEdit',
            Events::NOTIFICATION_EVENT_RESERVATION => 'onEventReservation'
        ];
    }

    public function onEventCreate(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_create'),
            'to' => $event->getParticipant()->getUser()->getEmail(),
            'from' => $this->sender,
            'template' => 'event_create',
            'templateParameters' => [
                'eventOwner' => $event->getOwner()->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventEdit(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_edit'),
            'to' => $event->getParticipant()->getUser()->getEmail(),
            'from' => $this->sender,
            'template' => 'event_edit',
            'templateParameters' => [
                'eventOwner' => $event->getOwner()->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventSwitchSkype(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_switch_skype'),
            'to' => $event->getOwner()->getEmail(),
            'from' => $this->sender,
            'template' => 'event_switch_skype',
            'templateParameters' => [
                'eventParticipant' => $event->getParticipant()->getUser()->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventCommentCreate(EventGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_comment_create'),
            'to' => $to,
            'from' => $this->sender,
            'template' => 'event_comment_create',
            'templateParameters' => [
                'commentOwner' => $user->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventCommentEdit(EventGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_comment_edit'),
            'to' => $to,
            'from' => $this->sender,
            'template' => 'event_comment_edit',
            'templateParameters' => [
                'commentOwner' => $user->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventReservation(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $this->trans('subject.event_reservation'),
            'to' => $event->getOwner()->getEmail(),
            'from' => $this->sender,
            'template' => 'event_create',
            'templateParameters' => [
                'eventOwner' => $event->getOwner()->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }
}
