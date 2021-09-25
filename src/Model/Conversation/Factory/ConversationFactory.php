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

namespace App\Model\Conversation\Factory;

use App\Model\BaseModelFactory;
use App\Entity\{
    User,
    Work,
    Conversation,
    ConversationType,
    ConversationMessage,
    ConversationParticipant,
    ConversationMessageStatus,
    ConversationMessageStatusType
};

class ConversationFactory extends BaseModelFactory
{
    public function createConversation(
        User $owner,
        int $type,
        Work $work = null,
        string $name = null
    ): Conversation {
        $conversation = new Conversation;
        $conversation->setOwner($owner);
        $conversation->setType($this->entityManagerService->getReference(ConversationType::class, $type));

        if ($work !== null) {
            $conversation->setWork($work);
        }

        if ($name !== null) {
            $conversation->setName($name);
        }

        $this->entityManagerService->persistAndFlush($conversation);

        return $conversation;
    }

    public function createConversationMessage(
        Conversation $conversation,
        User $owner,
        string $content
    ): ConversationMessage {
        $conversationMessage = new ConversationMessage;
        $conversationMessage->setConversation($conversation);
        $conversationMessage->setContent($content);
        $conversationMessage->setOwner($owner);

        $this->entityManagerService->persistAndFlush($conversationMessage);

        return $conversationMessage;
    }

    public function createConversationParticipant(
        Conversation $conversation,
        iterable $participants
    ): void {
        foreach ($participants as $participant) {
            $conversationParticipant = new ConversationParticipant;
            $conversationParticipant->setConversation($conversation);
            $conversationParticipant->setUser($this->getUser($participant));
            $this->entityManagerService->persistAndFlush($conversationParticipant);
        }
    }

    public function createConversationMessageStatus(
        Conversation $conversation,
        ConversationMessage $message,
        User $user,
        iterable $participants,
        int $type
    ): void {
        foreach ($participants as $participant) {
            $participant = $this->getUser($participant);

            if ($participant->getId() !== $user->getId()) {
                $messageStatus = new ConversationMessageStatus;
                $messageStatus->setConversation($conversation);
                $messageStatus->setMessage($message);
                $messageStatus->setType($this->entityManagerService->getReference(ConversationMessageStatusType::class, $type));
                $messageStatus->setUser($participant);
                $this->entityManagerService->persistAndFlush($messageStatus);
            }
        }

        $this->entityManagerService->flush();
    }

    public function getUser(ConversationParticipant|User $user): User
    {
        return $user instanceof ConversationParticipant ? $user->getUser() : $user;
    }
}
