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
        private readonly EntityManagerService $entityManagerService,
        private readonly ConversationStatusService $conversationStatusService,
        private readonly ConversationMessageRepository $conversationMessageRepository
    ) {}

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
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::UNREAD
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);
                    break;
                case  ConversationMessageStatusTypeConstant::UNREAD:
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::READ
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);
                    break;
            }

            $this->entityManagerService->flush($conversationMessageStatus);
        } else {

            /** @var ConversationMessageStatusType $conversationMessageStatusType */
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::UNREAD
            );

            $newConversationMessageStatus = new ConversationMessageStatus;
            $newConversationMessageStatus->setConversation($conversation);
            $newConversationMessageStatus->setMessage($conversationMessage);
            $newConversationMessageStatus->setUser($user);
            $newConversationMessageStatus->setType($conversationMessageStatusType);

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
        /** @var ConversationMessageStatusType $ConversationMessageStatusType */
        $ConversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD
        );

        $conversationMessage = $this->conversationMessageRepository->allByUserStatus(
            $user,
            $ConversationMessageStatusType
        );

        if ($limit !== null) {
            $conversationMessage->setMaxResults($limit);
        }

        return $conversationMessage->getQuery()->getResult();
    }

    public function getTotalUnreadMessagesByUser(User $user): int
    {
        /** @var ConversationMessageStatusType $ConversationMessageStatusType */
        $ConversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD
        );

        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus(
                $user,
                $ConversationMessageStatusType
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
        /** @var ConversationMessageStatusType $ConversationMessageStatusType */
        $ConversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD
        );

        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus(
                $user,
                $ConversationMessageStatusType
            )
            ->andWhere('conversation_message.createdAt >= :afterDate')
            ->setParameter('afterDate', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
