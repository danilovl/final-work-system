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
    Event,
    Comment
};
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

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

    public function onEventCreate(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_create');
        $to = $event->getParticipant()->getUser()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_create'), [
            'user' => $event->getOwner(),
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onEventEdit(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_edit');
        $to = $event->getParticipant()->getUser()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_edit'), [
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onEventSwitchSkype(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_switch_skype');
        $to = $event->getOwner()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_switch_skype'), [
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onEventCommentCreate(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $subject = $this->trans('subject.event_comment_create');
        $body = $this->twig->render($this->getTemplate('event_comment_create'), [
            'user' => $user,
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onEventCommentEdit(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $subject = $this->trans('subject.event_comment_edit');
        $body = $this->twig->render($this->getTemplate('event_comment_edit'), [
            'user' => $user,
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onEventReservation(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_reservation');
        $to = $event->getOwner()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_create'), [
            'user' => $event->getParticipant()->getUser(),
            'event' => $event
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }
}