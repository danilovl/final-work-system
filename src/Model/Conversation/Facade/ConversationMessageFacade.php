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

namespace App\Model\Conversation\Facade;

use App\Repository\ConversationMessageRepository;
use App\Model\Conversation\Service\ConversationStatusService;
use DateTime;
use App\Service\EntityManagerService;
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
    private ConversationMessageRepository $conversationMessageRepository;

    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationStatusService $conversationStatusService
    ) {
        $this->conversationMessageRepository = $entityManagerService->getRepository(ConversationMessage::class);
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

        /** @var ConversationMessageStatus|null $conversationMessageStatus */
        $conversationMessageStatus = $this->conversationStatusService
            ->getConversationMessageStatus($conversationMessage, $user);

        if ($conversationMessageStatus !== null) {
            switch ($conversationMessageStatus->getType()->getId()) {
                case ConversationMessageStatusTypeConstant::READ:
                    $conversationMessageStatus->setType(
                        $this->entityManagerService->getReference(
                            ConversationMessageStatusType::class,
                            ConversationMessageStatusTypeConstant::UNREAD
                        )
                    );
                    break;
                case  ConversationMessageStatusTypeConstant::UNREAD:
                    $conversationMessageStatus->setType(
                        $this->entityManagerService->getReference(
                            ConversationMessageStatusType::class,
                            ConversationMessageStatusTypeConstant::READ
                        )
                    );
                    break;
            }

            $this->entityManagerService->flush($conversationMessageStatus);
        } else {
            $newConversationMessageStatus = new ConversationMessageStatus();
            $newConversationMessageStatus->setConversation($conversation);
            $newConversationMessageStatus->setMessage($conversationMessage);
            $newConversationMessageStatus->setUser($user);
            $newConversationMessageStatus->setType(
                $this->entityManagerService->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            );

            $this->entityManagerService->persistAndFlush($newConversationMessageStatus);
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
                $this->entityManagerService->getReference(
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
                $this->entityManagerService->getReference(
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

    public function getMessagesByConversationAfterDate(
        Conversation $conversation,
        DateTime $date
    ): array {
        return $this->conversationMessageRepository
            ->allByConversationAfterDate($conversation, $date)
            ->getQuery()
            ->getResult();
    }

    public function getTotalUnreadMessagesAfterDateByUser(User $user, DateTime $date): int
    {
        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus(
                $user,
                $this->entityManagerService->getReference(
                    ConversationMessageStatusType::class,
                    ConversationMessageStatusTypeConstant::UNREAD
                )
            )
            ->andWhere('conversation_message.createdAt >= :afterDate')
            ->setParameter('afterDate', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
