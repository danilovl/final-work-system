<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\EventDispatcher;

use App\EventDispatcher\GenericEvent\EventGenericEvent;
use App\Entity\{
    Event,
    Comment
};
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventEventDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onEventComment(Comment $comment, bool $isEventCommentExist): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->comment = $comment;

        $this->eventDispatcher->dispatch(
            $genericEvent,
            $isEventCommentExist ? Events::NOTIFICATION_EVENT_COMMENT_EDIT : Events::NOTIFICATION_EVENT_COMMENT_CREATE
        );

        $this->eventDispatcher->dispatch(
            $genericEvent,
            $isEventCommentExist ? Events::SYSTEM_EVENT_COMMENT_EDIT : Events::SYSTEM_EVENT_COMMENT_CREATE
        );
    }

    public function onEventEdit(Event $event): void
    {
        if ($event->getParticipant()) {
            $genericEvent = new EventGenericEvent;
            $genericEvent->event = $event;

            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_EDIT);

            if ($event->getParticipant()->getUser()) {
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_EDIT);
            }
        }
    }

    public function onEventSwitchToSkype(Event $event): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_SWITCH_SKYPE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_SWITCH_SKYPE);
    }

    public function onEventCalendarCreate(Event $event, bool $eventParticipant): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        if ($eventParticipant) {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_CREATE);
        }
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_CREATE);
    }

    public function onEventCalendarReservation(Event $event): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_RESERVATION);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_RESERVATION);
    }

    public function onEventCalendarEdit(Event $event): void
    {
        if ($event->getParticipant()) {
            $genericEvent = new EventGenericEvent;
            $genericEvent->event = $event;

            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_EDIT);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_EDIT);
        }
    }
}
