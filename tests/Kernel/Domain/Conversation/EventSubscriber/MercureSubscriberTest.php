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

namespace Domain\Conversation\EventSubscriber;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\EventDispatcher\GenericEvent\ConversationMessageGenericEvent;
use App\Domain\Conversation\EventSubscriber\MercureSubscriber;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\User\Entity\User;
use App\Tests\Mock\Application\Traits\SecurityLoginTraitMock;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MercureSubscriberTest extends KernelTestCase
{
    use SecurityLoginTraitMock;

    private ConversationMessage $conversationMessage;

    private MercureSubscriber $mercureSubscriber;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->mercureSubscriber = $kernel->getContainer()->get(MercureSubscriber::class);

        $user = new User;
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setFirstname('test');
        $user->setLastname('test');

        $userParticipant = clone $user;
        $userParticipant->setId(2);

        $conversationParticipant = new ConversationParticipant;
        $conversationParticipant->setUser($userParticipant);

        $conversation = new Conversation;
        $conversation->setId(1);
        $conversation->setOwner($user);
        $conversation->setParticipants(new ArrayCollection([$conversationParticipant]));
        $conversation->createUpdateAblePrePersist();

        $conversationMessage = new ConversationMessage;
        $conversationMessage->setId(1);
        $conversationMessage->setOwner($user);
        $conversationMessage->setContent('test');
        $conversationMessage->setConversation($conversation);
        $conversationMessage->createUpdateAblePrePersist();

        $this->conversationMessage = $conversationMessage;

        $this->loginUser($kernel, $user);
    }

    public function testOnMessageCreateConversation(): void
    {
        $event = new ConversationMessageGenericEvent($this->conversationMessage);

        $this->mercureSubscriber->onMessageCreateConversation($event);

        $this->assertTrue(true);
    }

    public function testOnMessageCreateUnreadConversationMessageWidget(): void
    {
        $event = new ConversationMessageGenericEvent($this->conversationMessage);

        $this->mercureSubscriber->onMessageCreateUnreadConversationMessageWidget($event);

        $this->assertTrue(true);
    }
}
