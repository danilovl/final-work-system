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

namespace App\Domain\Conversation\Facade;

use App\Application\Constant\ConversationMessageStatusTypeConstant;
use App\Application\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Repository\ConversationMessageRepository;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use DateTime;
use Doctrine\ORM\Query;

class ConversationMessageFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationStatusService $conversationStatusService,
        private ConversationMessageRepository $conversationMessageRepository
    ) {
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
            $newConversationMessageStatus = new ConversationMessageStatus;
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
