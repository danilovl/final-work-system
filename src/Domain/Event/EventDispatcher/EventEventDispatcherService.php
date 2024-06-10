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
use App\Domain\Event\EventDispatcher\GenericEvent\{
    EventGenericEvent,
    EventCommentGenericEvent
};
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class EventEventDispatcherService
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onEventComment(Comment $comment, bool $isEventCommentExist): void
    {
        $genericEvent = new EventCommentGenericEvent($comment);

        $this->asyncService->add(function () use ($genericEvent, $isEventCommentExist): void {
            $this->eventDispatcher->dispatch(
                $genericEvent,
                $isEventCommentExist ? Events::EVENT_COMMENT_EDIT : Events::EVENT_COMMENT_CREATE
            );
        });
    }

    public function onEventEdit(Event $event): void
    {
        $genericEvent = new EventGenericEvent($event);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::EVENT_EDIT);
        });
    }

    public function onEventSwitchToSkype(Event $event): void
    {
        $genericEvent = new EventGenericEvent($event);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::EVENT_SWITCH_SKYPE);
        });
    }

    public function onEventCalendarCreate(Event $event): void
    {
        $genericEvent = new EventGenericEvent($event);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::EVENT_CREATE);
        });
    }

    public function onEventCalendarReservation(Event $event): void
    {
        $genericEvent = new EventGenericEvent($event);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::EVENT_RESERVATION);
        });
    }

    public function onEventCalendarEdit(Event $event): void
    {
        $genericEvent = new EventGenericEvent($event);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::EVENT_EDIT);
        });
    }
}
