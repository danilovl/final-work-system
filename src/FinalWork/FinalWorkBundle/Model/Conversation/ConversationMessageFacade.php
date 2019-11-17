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

use FinalWork\FinalWorkBundle\Services\ConversationStatusService;
use Doctrine\ORM\{
    Query,
    ORMException,
    EntityManager,
    NonUniqueResultException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Constant\ConversationMessageStatusTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Conversation,
    ConversationMessage,
    ConversationMessageStatus,
    ConversationMessageStatusType,
    Repository\ConversationMessageRepository
};
use FinalWork\SonataUserBundle\Entity\User;

class ConversationMessageFacade
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ConversationMessageRepository
     */
    private $conversationMessageRepository;

    /**
     * @var ConversationStatusService
     */
    private $conversationStatusService;

    /**
     * ConversationMessageService constructor.
     * @param EntityManager $entityManager
     * @param ConversationStatusService $conversationStatusService
     */
    public function __construct(
        EntityManager $entityManager,
        ConversationStatusService $conversationStatusService
    ) {
        $this->em = $entityManager;
        $this->conversationMessageRepository = $entityManager->getRepository(ConversationMessage::class);
        $this->conversationStatusService = $conversationStatusService;
    }

    /**
     * @param int $id
     * @return ConversationMessage|null
     */
    public function find(int $id): ?ConversationMessage
    {
        return $this->conversationMessageRepository->find($id);
    }

    /**
     * @param User $user
     * @param ConversationMessage $conversationMessage
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function changeReadMessageStatus(
        User $user,
        ConversationMessage $conversationMessage
    ): void {
        $conversation = $conversationMessage->getConversation();

        /** @var ConversationMessageStatus $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationStatusService
            ->getConversationMessageStatus($conversationMessage, $user);

        if ($conversationMessageStatus) {
            switch ($conversationMessageStatus->getType()->getId()) {
                case ConversationMessageStatusTypeConstant::READ:
                    $conversationMessageStatus->setType(
                        $this->em->getReference(
                            ConversationMessageStatusType::class,
                            ConversationMessageStatusTypeConstant::UNREAD
                        )
                    );
                    break;
                case  ConversationMessageStatusTypeConstant::UNREAD:
                    $conversationMessageStatus->setType(
                        $this->em->getReference(
                            ConversationMessageStatusType::class,
                            ConversationMessageStatusTypeConstant::READ
                        )
                    );
                    break;
            }

            $this->em->flush($conversationMessageStatus);
        } else {
            $newConversationMessageStatus = new ConversationMessageStatus();
            $newConversationMessageStatus->setConversation($conversation);
            $newConversationMessageStatus->setMessage($conversationMessage);
            $newConversationMessageStatus->setUser($user);
            $newConversationMessageStatus->setType(
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            );

            $this->em->persist($newConversationMessageStatus);
            $this->em->flush($newConversationMessageStatus);
        }
    }

    /**
     * @param Conversation $conversation
     * @param int $limit
     * @return array
     */
    public function getMessagesByConversation(Conversation $conversation, int $limit): array
    {
        return $this->conversationMessageRepository
            ->findAllByConversation($conversation, $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Conversation $conversation
     * @return Query
     */
    public function queryMessagesByConversation(Conversation $conversation): Query
    {
        return $this->conversationMessageRepository
            ->findAllByConversation($conversation)
            ->getQuery();
    }

    /**
     * @param User $user
     * @param int|null $limit
     * @return array
     *
     * @throws ORMException
     */
    public function getUnreadMessagesByUser(User $user, ?int $limit = null): array
    {
        $conversationMessage = $this->conversationMessageRepository
            ->findAllByUserStatus(
                $user,
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            );

        if ($limit !== null) {
            $conversationMessage->setMaxResults($limit);
        }

        return $conversationMessage->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return int
     *
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function getTotalUnreadMessagesByUser(User $user): int
    {
        return (int)$this->conversationMessageRepository
            ->getCountMessagesByUserStatus(
                $user,
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            )
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param iterable $conversations
     * @param User $user
     *
     * @throws ORMException
     */
    public function setIsReadToConversationMessages(iterable $conversations, User $user): void
    {
        /** @var ConversationMessage $conversationMessage */
        foreach ($conversations as $conversationMessage) {
            $conversationMessage->setRead(
                $this->conversationStatusService
                    ->isConversationMessageRead($conversationMessage, $user)
            );
        }
    }
}
