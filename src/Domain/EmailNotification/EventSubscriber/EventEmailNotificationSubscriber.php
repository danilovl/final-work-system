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

namespace App\Domain\EmailNotification\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use App\Domain\Event\EventDispatcher\GenericEvent\{
    EventGenericEvent,
    EventCommentGenericEvent
};
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_CREATE => 'onEventCreate',
            Events::EVENT_EDIT => 'onEventEdit',
            Events::EVENT_SWITCH_SKYPE => 'onEventSwitchSkype',
            Events::EVENT_COMMENT_CREATE => 'onEventCommentCreate',
            Events::EVENT_COMMENT_EDIT => 'onEventCommentEdit',
            Events::EVENT_RESERVATION => 'onEventReservation'
        ];
    }

    public function onEventCreate(EventGenericEvent $genericEvent): void
    {
        $event = $genericEvent->event;
        if ($event->getParticipant() === null) {
            return;
        }

        $toUser = $event->getParticipantMust()->getUserMust();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.event_create',
            'to' => $toUser->getEmail(),
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
        if ($event->getParticipant() === null) {
            return;
        }

        $toUser = $event->getParticipantMust()->getUserMust();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.event_edit',
            'to' => $toUser->getEmail(),
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
        $toUser = $event->getOwner();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.event_switch_skype',
            'to' => $toUser->getEmail(),
            'from' => $this->sender,
            'template' => 'event_switch_skype',
            'templateParameters' => [
                'eventParticipant' => $event->getParticipantMust()->getUserMust()->getFullNameDegree(),
                'eventId' => $event->getId()
            ]
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onEventCommentCreate(EventCommentGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $toUser = $event->getParticipantMust()->getUserMust();
        $locale = $toUser->getLocale();

        $to = $toUser->getEmail();
        $user = $event->getParticipantMust()->getUserMust();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();

            $locale = $eventComment->getOwner()->getLocale();
        }

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $locale ?? $this->locale,
            'subject' => 'subject.event_comment_create',
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

    public function onEventCommentEdit(EventCommentGenericEvent $genericEvent): void
    {
        $eventComment = $genericEvent->comment;
        $event = $eventComment->getEvent();

        $toUser = $event->getParticipantMust()->getUserMust();
        $locale = $toUser->getLocale();

        $to = $toUser->getEmail();
        $user = $toUser;

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();

            $locale = $eventComment->getOwner()->getLocale();
        }

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $locale ?? $this->locale,
            'subject' => 'subject.event_comment_edit',
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
        $toUser = $event->getOwner();

        $emailNotificationToQueueData = EmailNotificationMessage::createFromArray([
            'locale' => $toUser->getLocale() ?? $this->locale,
            'subject' => 'subject.event_reservation',
            'to' => $toUser->getEmail(),
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
