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

namespace App\Model\Conversation;

use App\Services\{
    EntityManagerService,
    ConversationStatusService,
    ConversationVariationService
};
use App\Model\BaseModelFactory;
use App\Repository\ConversationRepository;
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
    private ConversationRepository $conversationRepository;
    private ConversationStatusService $conversationStatusService;
    private ConversationVariationService $conversationVariationService;
    private ConversationMessageFacade $conversationMessageService;

    public function __construct(
        EntityManagerService $entityManager,
        ConversationMessageFacade $conversationMessageFacade,
        ConversationStatusService $conversationStatusService,
        ConversationVariationService $conversationVariationService
    ) {
        parent::__construct($entityManager);

        $this->conversationRepository = $entityManager->getRepository(Conversation::class);
        $this->conversationStatusService = $conversationStatusService;
        $this->conversationVariationService = $conversationVariationService;
        $this->conversationMessageService = $conversationMessageFacade;
    }

    public function createConversation(
        User $owner,
        int $type,
        Work $work = null,
        string $name = null
    ): Conversation {
        $conversation = new Conversation;
        $conversation->setOwner($owner);
        $conversation->setType($this->em->getReference(ConversationType::class, $type));

        if ($work !== null) {
            $conversation->setWork($work);
        }

        if ($name !== null) {
            $conversation->setName($name);
        }

        $this->em->persistAndFlush($conversation);

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

        $this->em->persistAndFlush($conversationMessage);

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
            $this->em->persistAndFlush($conversationParticipant);
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
                $messageStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $type));
                $messageStatus->setUser($participant);
                $this->em->persistAndFlush($messageStatus);
            }
        }

        $this->em->flush();
    }

    public function getUser($user)
    {
        if ($user instanceof ConversationParticipant) {
            return $user instanceof User ? $user : $user->getUser();
        }

        return $user;
    }
}
