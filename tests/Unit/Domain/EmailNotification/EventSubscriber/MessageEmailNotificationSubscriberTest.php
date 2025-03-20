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

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\EmailNotification\EventSubscriber\MessageEmailNotificationSubscriber;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class MessageEmailNotificationSubscriberTest extends AbstractBaseEmailNotificationSubscriber
{
    protected static string $classSubscriber = MessageEmailNotificationSubscriber::class;

    protected readonly MessageEmailNotificationSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new MessageEmailNotificationSubscriber(
            $this->userFacade,
            $this->twigRenderService,
            $this->translator,
            $this->emailNotificationFactory,
            $this->parameterService,
            $this->bus
        );
    }

    public function testOnMessageCreate(): void
    {
        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $conversationParticipant = new ConversationParticipant;
        $conversationParticipant->setUser($user);

        $userTwo = clone $user;
        $userTwo->setId(2);

        $conversationParticipant = new ConversationParticipant;
        $conversationParticipant->setUser($user);

        $participants = new ArrayCollection;
        $participants->add($conversationParticipant);

        $conversationParticipant = new ConversationParticipant;
        $conversationParticipant->setUser($userTwo);
        $participants->add($conversationParticipant);

        $conversation = new Conversation;
        $conversation->setId(1);
        $conversation->setOwner($user);
        $conversation->setParticipants($participants);

        $conversationMessage = new ConversationMessage;
        $conversationMessage->setConversation($conversation);
        $conversationMessage->setOwner($user);

        $event = new ConversationMessageGenericEvent($conversationMessage);

        $this->subscriber->onMessageCreate($event);

        $this->assertTrue(true);
    }
}
