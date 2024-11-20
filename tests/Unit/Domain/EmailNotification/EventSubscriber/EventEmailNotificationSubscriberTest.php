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

namespace App\Tests\Unit\Domain\EmailNotification\EventSubscriber;

use App\Domain\Comment\Entity\Comment;
use App\Domain\EmailNotification\EventSubscriber\EventEmailNotificationSubscriber;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\EventDispatcher\GenericEvent\EventCommentGenericEvent;
use App\Domain\Event\EventDispatcher\GenericEvent\EventGenericEvent;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\User\Entity\User;

class EventEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = EventEmailNotificationSubscriber::class;
    protected readonly EventEmailNotificationSubscriber $subscriber;

    private readonly Event $eventWithParticipant;
    private readonly Event $eventWithoutParticipant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new EventEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus
        );

        $user = new User;
        $user->setId(1);
        $user->setFirstname('first');
        $user->setLastname('last');
        $user->setEmail('test@example.com');

        $this->eventWithParticipant = new Event;
        $this->eventWithParticipant->setId(1);
        $this->eventWithParticipant->setOwner($user);
        $eventParticipant = new EventParticipant;
        $eventParticipant->setUser($user);

        $this->eventWithParticipant->setParticipant($eventParticipant);

        $this->eventWithoutParticipant = new Event;
        $this->eventWithoutParticipant->setId(1);
        $this->eventWithoutParticipant->setOwner($user);

        $this->eventWithoutParticipant->setParticipant(null);
    }

    public function testEventGenericEvent(): void
    {
        $event = new EventGenericEvent($this->eventWithParticipant);

        $this->subscriber->onEventCreate($event);
        $this->subscriber->onEventEdit($event);
        $this->subscriber->onEventSwitchSkype($event);
        $this->subscriber->onEventReservation($event);

        $this->assertTrue(true);
    }

    public function testEventGenericEventNoParticipant(): void
    {
        $event = new EventGenericEvent($this->eventWithoutParticipant);

        $this->subscriber->onEventCreate($event);
        $this->subscriber->onEventEdit($event);

        $this->assertTrue(true);
    }

    public function testEventCommentGenericEvent(): void
    {
        $user = clone $this->eventWithParticipant->getOwner();
        $user->setId(2);

        $comment = new Comment;
        $comment->setEvent($this->eventWithParticipant);
        $comment->setOwner($user);

        $event = new EventCommentGenericEvent($comment);

        $this->subscriber->onEventCommentCreate($event);
        $this->subscriber->onEventCommentEdit($event);

        $this->assertTrue(true);
    }
}
