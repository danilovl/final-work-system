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

namespace App\Domain\Conversation\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

class ConversationFactory extends BaseModelFactory
{
    public function createConversation(
        User $owner,
        int $type,
        ?Work $work = null,
        ?string $name = null
    ): Conversation {
        /** @var ConversationType $conversationType */
        $conversationType = $this->entityManagerService->getReference(ConversationType::class, $type);

        $conversation = new Conversation;
        $conversation->setOwner($owner);
        $conversation->setType($conversationType);

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
                /** @var ConversationMessageStatusType $conversationMessageStatusType */
                $conversationMessageStatusType = $this->entityManagerService->getReference(ConversationMessageStatusType::class, $type);

                $messageStatus = new ConversationMessageStatus;
                $messageStatus->setConversation($conversation);
                $messageStatus->setMessage($message);
                $messageStatus->setType($conversationMessageStatusType);
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
