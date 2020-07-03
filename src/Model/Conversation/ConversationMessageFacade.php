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

use App\Repository\ConversationMessageRepository;
use App\Services\{
    EntityManagerService,
    ConversationStatusService
};
use Doctrine\ORM\Query;
use App\Constant\ConversationMessageStatusTypeConstant;
use App\Entity\{
    Conversation,
    ConversationMessage,
    ConversationMessageStatus,
    ConversationMessageStatusType
};
use App\Entity\User;

class ConversationMessageFacade
{
    private EntityManagerService $em;
    private ConversationMessageRepository $conversationMessageRepository;
    private ConversationStatusService $conversationStatusService;

    public function __construct(
        EntityManagerService $entityManager,
        ConversationStatusService $conversationStatusService
    ) {
        $this->em = $entityManager;
        $this->conversationMessageRepository = $entityManager->getRepository(ConversationMessage::class);
        $this->conversationStatusService = $conversationStatusService;
    }

    public function find(int $id): ?ConversationMessage
    {
        return $this->conversationMessageRepository->find($id);
    }

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

    public function getMessagesByConversation(Conversation $conversation, int $limit): array
    {
        return $this->conversationMessageRepository
            ->allByConversation($conversation, $limit)
            ->getQuery()
            ->getResult();
    }

    public function queryMessagesByConversation(Conversation $conversation): Query
    {
        return $this->conversationMessageRepository
            ->allByConversation($conversation)
            ->getQuery();
    }

    public function getUnreadMessagesByUser(User $user, ?int $limit = null): array
    {
        $conversationMessage = $this->conversationMessageRepository
            ->allByUserStatus(
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

    public function getTotalUnreadMessagesByUser(User $user): int
    {
        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus(
                $user,
                $this->em->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            )
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isUnreadMessagesByRecipient(User $user): bool
    {
        return $this->getTotalUnreadMessagesByUser($user) > 0;
    }

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
