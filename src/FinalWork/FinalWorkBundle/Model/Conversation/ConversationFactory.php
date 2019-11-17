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

namespace FinalWork\FinalWorkBundle\Model\Conversation;

use FinalWork\FinalWorkBundle\Services\{
    ConversationStatusService,
    ConversationVariationService
};
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    EntityManager,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Entity\Repository\ConversationRepository;
use FinalWork\FinalWorkBundle\Entity\{Work,
    Conversation,
    ConversationType,
    ConversationMessage,
    ConversationParticipant,
    ConversationMessageStatus,
    ConversationMessageStatusType
};
use FinalWork\SonataUserBundle\Entity\User;

class ConversationFactory extends BaseModelFactory
{
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * @var ConversationStatusService
     */
    private $conversationStatusService;

    /**
     * @var ConversationVariationService
     */
    private $conversationVariationService;

    /**
     * @var ConversationMessageFacade
     */
    private $conversationMessageService;

    /**
     * ConversationFactory constructor.
     * @param EntityManager $entityManager
     * @param ConversationMessageFacade $conversationMessageFacade
     * @param ConversationStatusService $conversationStatusService
     * @param ConversationVariationService $conversationVariationService
     */
    public function __construct(
        EntityManager $entityManager,
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

    /**
     * @param User $owner
     * @param int $type
     * @param Work|null $work
     * @param string|null $name
     * @return Conversation
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
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

        $this->em->persist($conversation);
        $this->em->flush($conversation);

        return $conversation;
    }

    /**
     * @param Conversation $conversation
     * @param User $owner
     * @param string $content
     * @return ConversationMessage
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createConversationMessage(
        Conversation $conversation,
        User $owner,
        string $content
    ): ConversationMessage {
        $conversationMessage = new ConversationMessage;
        $conversationMessage->setConversation($conversation);
        $conversationMessage->setContent($content);
        $conversationMessage->setOwner($owner);

        $this->em->persist($conversationMessage);
        $this->em->flush($conversationMessage);

        return $conversationMessage;
    }

    /**
     * @param Conversation $conversation
     * @param iterable $participants
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createConversationParticipant(
        Conversation $conversation,
        iterable $participants
    ): void {
        foreach ($participants as $participant) {
            $conversationParticipant = new ConversationParticipant();
            $conversationParticipant->setConversation($conversation);
            $conversationParticipant->setUser($this->getUser($participant));
            $this->em->persist($conversationParticipant);
        }

        $this->em->flush();
    }

    /**
     * @param Conversation $conversation
     * @param ConversationMessage $message
     * @param User $user
     * @param iterable $participants
     * @param int $type
     * @throws ORMException
     * @throws OptimisticLockException
     */
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
                $messageStatus = new ConversationMessageStatus();
                $messageStatus->setConversation($conversation);
                $messageStatus->setMessage($message);
                $messageStatus->setType($this->em->getReference(ConversationMessageStatusType::class, $type));
                $messageStatus->setUser($participant);
                $this->em->persist($messageStatus);
            }
        }

        $this->em->flush();
    }

    /**
     * @param ConversationParticipant|User $user
     * @return ConversationParticipant|User
     */
    public function getUser($user)
    {
        if ($user instanceof ConversationParticipant) {
            return $user instanceof User ? $user : $user->getUser();
        }

        return $user;
    }
}
