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

namespace App\Domain\Event\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\GenericEvent\EventGenericEvent;
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventEventDispatcherService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncService $asyncService
    ) {}

    public function onEventComment(Comment $comment, bool $isEventCommentExist): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->comment = $comment;

        $this->asyncService->add(function () use ($genericEvent, $isEventCommentExist): void {
            $this->eventDispatcher->dispatch(
                $genericEvent,
                $isEventCommentExist ? Events::NOTIFICATION_EVENT_COMMENT_EDIT : Events::NOTIFICATION_EVENT_COMMENT_CREATE
            );

            $this->eventDispatcher->dispatch(
                $genericEvent,
                $isEventCommentExist ? Events::SYSTEM_EVENT_COMMENT_EDIT : Events::SYSTEM_EVENT_COMMENT_CREATE
            );
        });

    }

    public function onEventEdit(Event $event): void
    {
        if ($event->getParticipant() === null) {
            return;
        }

        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->asyncService->add(function () use ($genericEvent, $event): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_EDIT);

            if ($event->getParticipant()->getUser()) {
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_EDIT);
            }
        });
    }

    public function onEventSwitchToSkype(Event $event): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_SWITCH_SKYPE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_SWITCH_SKYPE);
        });
    }

    public function onEventCalendarCreate(Event $event, bool $eventParticipant): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;


        $this->asyncService->add(function () use ($genericEvent, $eventParticipant): void {
            if ($eventParticipant) {
                $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_CREATE);
            }

            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_CREATE);
        });
    }

    public function onEventCalendarReservation(Event $event): void
    {
        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_RESERVATION);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_RESERVATION);
        });
    }

    public function onEventCalendarEdit(Event $event): void
    {
        if ($event->getParticipant() === null) {
            return;
        }

        $genericEvent = new EventGenericEvent;
        $genericEvent->event = $event;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_EVENT_EDIT);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_EVENT_EDIT);
        });
    }
}
