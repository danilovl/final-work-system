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

namespace App\Tests\Unit\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\EventEmailNotificationSubscriber;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\GenericEvent\EventCommentGenericEvent;
use App\Domain\Event\EventDispatcher\GenericEvent\EventGenericEvent;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Entity\User;

class EventEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = EventEmailNotificationSubscriber::class;
    protected readonly EventEmailNotificationSubscriber $subscriber;
    private readonly Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new EventEmailNotificationSubscriber(
            $this->userFacade,
            $this->twig,
            $this->translator,
            $this->emailNotificationQueueFactory,
            $this->parameterService,
            $this->bus
        );

        $user = new User;
        $user->setFirstname('first');
        $user->setLastname('last');
        $user->setEmail('test@example.com');

        $this->event = new Event;
        $this->event->setId(1);
        $this->event->setOwner($user);
        $eventParticipant = new EventParticipant;
        $eventParticipant->setUser($user);

        $this->event->setParticipant($eventParticipant);
    }

    public function testEventGenericEvent(): void
    {
        $event = new EventGenericEvent($this->event);

        $this->subscriber->onEventCreate($event);
        $this->subscriber->onEventEdit($event);
        $this->subscriber->onEventSwitchSkype($event);
        $this->subscriber->onEventReservation($event);

        $this->assertTrue(true);
    }

    public function testEventCommentGenericEvent(): void
    {
        $user = clone $this->event->getOwner();
        $user->setId(2);

        $comment = new Comment;
        $comment->setEvent($this->event);
        $comment->setOwner($user);

        $event = new EventCommentGenericEvent($comment);

        $this->subscriber->onEventCommentCreate($event);
        $this->subscriber->onEventCommentEdit($event);

        $this->assertTrue(true);
    }
}
