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

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Repository\ConversationMessageRepository;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use DateTimeImmutable;
use Doctrine\ORM\Query;
use Webmozart\Assert\Assert;

readonly class ConversationMessageFacade
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ConversationStatusService $conversationStatusService,
        private ConversationMessageRepository $conversationMessageRepository
    ) {}

    public function getConversationMessage(int $id): ConversationMessage
    {
        /** @var ConversationMessage $conversationMessage */
        $conversationMessage = $this->conversationMessageRepository->find($id);

        return $conversationMessage;
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
                case ConversationMessageStatusTypeConstant::READ->value:
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::UNREAD->value
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);

                    break;
                case ConversationMessageStatusTypeConstant::UNREAD->value:
                    /** @var ConversationMessageStatusType $conversationMessageStatusType */
                    $conversationMessageStatusType = $this->entityManagerService->getReference(
                        ConversationMessageStatusType::class,
                        ConversationMessageStatusTypeConstant::READ->value
                    );

                    $conversationMessageStatus->setType($conversationMessageStatusType);

                    break;
            }

            $this->entityManagerService->flush();
        } else {

            /** @var ConversationMessageStatusType $conversationMessageStatusType */
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::UNREAD->value
            );

            $newConversationMessageStatus = new ConversationMessageStatus;
            $newConversationMessageStatus->setConversation($conversation);
            $newConversationMessageStatus->setMessage($conversationMessage);
            $newConversationMessageStatus->setUser($user);
            $newConversationMessageStatus->setType($conversationMessageStatusType);

            $this->entityManagerService->persistAndFlush($newConversationMessageStatus);
        }
    }

    /**
     * @return ConversationMessage[]
     */
    public function getMessagesByConversation(Conversation $conversation, int $limit): array
    {
        /** @var ConversationMessage[] $result */
        $result = $this->conversationMessageRepository
            ->allByConversation($conversation, $limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryByWorkUser(Work $work, User $user): Query
    {
        return $this->conversationMessageRepository
            ->allByWorkUser($work, $user)
            ->getQuery();
    }

    public function queryMessagesByConversation(Conversation $conversation): Query
    {
        return $this->conversationMessageRepository
            ->allByConversation($conversation)
            ->getQuery();
    }

    /**
     * @param int[] $ids
     */
    public function queryByIds(array $ids): Query
    {
        Assert::allInteger($ids);

        return $this->conversationMessageRepository
            ->byIds($ids)
            ->getQuery();
    }

    /**
     * @param int[] $conversationIds
     * @return array<int, int>
     */
    public function getCountsByConversationIds(array $conversationIds): array
    {
        $counts = array_fill_keys($conversationIds, 0);
        if (count($conversationIds) === 0) {
            return $counts;
        }

        /** @var array<array{conversationId: int|string, messageCount: int|string}> $rows */
        $rows = $this->conversationMessageRepository
            ->countByConversationIds($conversationIds)
            ->getQuery()
            ->getArrayResult();

        foreach ($rows as $row) {
            $counts[(int) $row['conversationId']] = (int) $row['messageCount'];
        }

        return $counts;
    }

    /**
     * @param int[] $conversationIds
     * @return array<int, ConversationMessage|null>
     */
    public function getLastByConversationIds(array $conversationIds): array
    {
        $lastMessages = array_fill_keys($conversationIds, null);
        if (count($conversationIds) === 0) {
            return $lastMessages;
        }

        /** @var ConversationMessage[] $messages */
        $messages = $this->conversationMessageRepository
            ->lastByConversationIds($conversationIds)
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            $conversationId = $message->getConversation()->getId();
            $lastMessages[$conversationId] ??= $message;
        }

        return $lastMessages;
    }

    /**
     * @return ConversationMessage[]
     */
    public function getUnreadMessagesByUser(User $user, ?int $limit = null): array
    {
        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        $conversationMessage = $this->conversationMessageRepository->allByUserStatus(
            $user,
            $conversationMessageStatusType
        );

        if ($limit !== null) {
            $conversationMessage->setMaxResults($limit);
        }

        /** @var ConversationMessage[] $result */
        $result = $conversationMessage->getQuery()->getResult();

        return $result;
    }

    public function getTotalUnreadMessagesByUser(User $user): int
    {
        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus($user, $conversationMessageStatusType)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isUnreadMessagesByRecipient(User $user): bool
    {
        return $this->getTotalUnreadMessagesByUser($user) > 0;
    }

    /**
     * @return ConversationMessage[]
     */
    public function getMessagesByConversationAfterDate(
        Conversation $conversation,
        DateTimeImmutable $date
    ): array {
        /** @var ConversationMessage[] $result */
        $result = $this->conversationMessageRepository
            ->allByConversationAfterDate($conversation, $date)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getTotalUnreadMessagesAfterDateByUser(User $user, DateTimeImmutable $date): int
    {
        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        return (int) $this->conversationMessageRepository
            ->countMessagesByUserStatus($user, $conversationMessageStatusType)
            ->andWhere('conversation_message.createdAt >= :afterDate')
            ->setParameter('afterDate', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
