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

namespace App\Domain\Conversation\Service;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EntityManagerService;
use Symfony\Contracts\Service\ResetInterface;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\ConversationMessageStatus\Bus\Query\ConversationIdsByUserType\{GetConversationIdsByUserTypeQuery, GetConversationIdsByUserTypeQueryResult};
use App\Domain\ConversationMessageStatus\Bus\Query\MessageTypesByConversationUser\{GetMessageTypesByConversationUserQuery, GetMessageTypesByConversationUserQueryResult};
use App\Domain\ConversationMessageStatus\Bus\Query\ConversationMessageStatusByMessageUser\{GetConversationMessageStatusByMessageUserQuery, GetConversationMessageStatusByMessageUserQueryResult};

class ConversationStatusService implements ResetInterface
{
    /**
     * @var array<int, array<int, true>>
     */
    private array $unreadConversationIds = [];

    /**
     * @var array<int, array<int, array<int, int>>>
     */
    private array $messageTypeIds = [];

    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly QueryBusInterface $queryBus
    ) {}

    public function isConversationRead(
        Conversation $conversation,
        User $user
    ): bool {
        $unreadConversationIds = $this->getUnreadConversationIds($user);

        return !isset($unreadConversationIds[$conversation->getId()]);
    }

    public function isConversationMessageRead(
        ConversationMessage $conversationMessage,
        User $user,
        bool $switchToRead = false
    ): bool {
        if ($switchToRead) {
            $isRead = $this->isConversationMessageReadAndSwitch($conversationMessage, $user);
            $this->reset();

            return $isRead;
        }

        $messageTypeIds = $this->getMessageTypeIds($conversationMessage->getConversation()->getId(), $user);
        $typeId = $messageTypeIds[$conversationMessage->getId()] ?? null;
        if ($typeId === null) {
            return true;
        }

        return $typeId === ConversationMessageStatusTypeConstant::READ->value;
    }

    public function reset(): void
    {
        $this->unreadConversationIds = [];
        $this->messageTypeIds = [];
    }

    /**
     * @return array<int, true>
     */
    private function getUnreadConversationIds(User $user): array
    {
        $userId = $user->getId();
        if (isset($this->unreadConversationIds[$userId])) {
            return $this->unreadConversationIds[$userId];
        }

        /** @var ConversationMessageStatusType $type */
        $type = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::UNREAD->value
        );

        $getConversationIdsByUserTypeQuery = GetConversationIdsByUserTypeQuery::create(
            user: $user,
            type: $type
        );
        /** @var GetConversationIdsByUserTypeQueryResult $getConversationIdsByUserTypeQueryResult */
        $getConversationIdsByUserTypeQueryResult = $this->queryBus->handle($getConversationIdsByUserTypeQuery);
        $conversationIds = $getConversationIdsByUserTypeQueryResult->conversationIds;

        $unreadConversationIds = [];
        foreach ($conversationIds as $conversationId) {
            $unreadConversationIds[(int) $conversationId] = true;
        }

        $this->unreadConversationIds[$userId] = $unreadConversationIds;

        return $unreadConversationIds;
    }

    /**
     * @return array<int, int>
     */
    private function getMessageTypeIds(int $conversationId, User $user): array
    {
        $userId = $user->getId();
        if (isset($this->messageTypeIds[$userId][$conversationId])) {
            return $this->messageTypeIds[$userId][$conversationId];
        }

        $getMessageTypesByConversationUserQuery = GetMessageTypesByConversationUserQuery::create(
            conversationId: $conversationId,
            user: $user
        );
        /** @var GetMessageTypesByConversationUserQueryResult $getMessageTypesByConversationUserQueryResult */
        $getMessageTypesByConversationUserQueryResult = $this->queryBus->handle($getMessageTypesByConversationUserQuery);
        $rows = $getMessageTypesByConversationUserQueryResult->rows;

        $messageTypeIds = [];
        foreach ($rows as $row) {
            $messageTypeIds[(int) $row['messageId']] = (int) $row['typeId'];
        }

        $this->messageTypeIds[$userId][$conversationId] = $messageTypeIds;

        return $messageTypeIds;
    }

    private function isConversationMessageReadAndSwitch(
        ConversationMessage $conversationMessage,
        User $user
    ): bool {
        $getConversationMessageStatusByMessageUserQuery = GetConversationMessageStatusByMessageUserQuery::create(
            conversationMessage: $conversationMessage,
            user: $user
        );
        /** @var GetConversationMessageStatusByMessageUserQueryResult $getConversationMessageStatusByMessageUserQueryResult */
        $getConversationMessageStatusByMessageUserQueryResult = $this->queryBus->handle($getConversationMessageStatusByMessageUserQuery);
        $conversationMessageStatus = $getConversationMessageStatusByMessageUserQueryResult->conversationMessageStatus;

        if ($conversationMessageStatus === null) {
            return true;
        }

        if ($conversationMessageStatus->getType()->getId() === ConversationMessageStatusTypeConstant::READ->value) {
            return true;
        }

        /** @var ConversationMessageStatusType $conversationMessageStatusType */
        $conversationMessageStatusType = $this->entityManagerService->getReference(
            ConversationMessageStatusType::class,
            ConversationMessageStatusTypeConstant::READ->value
        );

        $conversationMessageStatus->setType($conversationMessageStatusType);

        $this->entityManagerService->persistAndFlush($conversationMessageStatus);

        return false;
    }
}
