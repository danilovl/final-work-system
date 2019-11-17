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

namespace FinalWork\FinalWorkBundle\Services\EventDispatcher;

use FinalWork\FinalWorkBundle\Entity\{
    Event,
    Comment
};
use FinalWork\FinalWorkBundle\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class EventEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * EventEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Comment $comment
     * @param bool $isEventCommentExist
     */
    public function onEventComment(Comment $comment, bool $isEventCommentExist): void
    {
        $genericEvent = new GenericEvent($comment);

        if ($isEventCommentExist) {
            $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_COMMENT_EDIT, $genericEvent);
            $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_COMMENT_EDIT, $genericEvent);
        } else {
            $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_COMMENT_CREATE, $genericEvent);
            $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_COMMENT_CREATE, $genericEvent);
        }
    }

    /**
     * @param Event $event
     */
    public function onEventEdit(Event $event): void
    {
        if ($event->getParticipant()) {
            $genericEvent = new GenericEvent($event);

            $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_EDIT, $genericEvent);

            if ($event->getParticipant()->getUser()) {
                $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_EDIT, $genericEvent);
            }
        }
    }

    /**
     * @param Event $event
     */
    public function onEventSwitchToSkype(Event $event): void
    {
        $genericEvent = new GenericEvent($event);

        $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_SWITCH_SKYPE, $genericEvent);
        $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_SWITCH_SKYPE, $genericEvent);
    }

    /**
     * @param Event $event
     * @param bool $eventParticipant
     */
    public function onEventCalendarCreate(Event $event, bool $eventParticipant): void
    {
        $genericEvent = new GenericEvent($event);

        if ($eventParticipant) {
            $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_CREATE, $genericEvent);
        }
        $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_CREATE, $genericEvent);
    }

    /**
     * @param Event $event
     */
    public function onEventCalendarReservation(Event $event): void
    {
        $genericEvent = new GenericEvent($event);

       $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_RESERVATION, $genericEvent);
       $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_RESERVATION, $genericEvent);
    }

    /**
     * @param Event $event
     */
    public function onEventCalendarEdit(Event $event): void
    {
        if ($event->getParticipant()) {
            $genericEvent = new GenericEvent($event);

            $this->eventDispatcher->dispatch(Events::NOTIFICATION_EVENT_EDIT, $genericEvent);
            $this->eventDispatcher->dispatch(Events::SYSTEM_EVENT_EDIT, $genericEvent);
        }
    }
}
