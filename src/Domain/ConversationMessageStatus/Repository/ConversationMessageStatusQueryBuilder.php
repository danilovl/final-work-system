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

namespace App\Domain\ConversationMessageStatus\Repository;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class ConversationMessageStatusQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinMessage(): self
    {
        $this->queryBuilder->leftJoin('conversation_message_status.message', 'message');

        return $this;
    }

    public function whereByUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.user = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function whereByConversation(Conversation $conversation): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->setParameter('conversation', $conversation);

        return $this;
    }

    public function whereByType(ConversationMessageStatusType $type): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.type = :type')
            ->setParameter('type', $type);

        return $this;
    }

    public function selectConversationId(): self
    {
        $this->queryBuilder
            ->select('IDENTITY(conversation_message_status.conversation) AS conversationId')
            ->distinct();

        return $this;
    }

    public function selectMessageIdAndTypeId(): self
    {
        $this->queryBuilder->select(
            'IDENTITY(conversation_message_status.message) AS messageId',
            'IDENTITY(conversation_message_status.type) AS typeId'
        );

        return $this;
    }

    public function whereByMessageConversationId(int $conversationId): self
    {
        $this->queryBuilder
            ->andWhere('message.conversation = :messageConversationId')
            ->setParameter('messageConversationId', $conversationId);

        return $this;
    }

    public function whereByMessage(ConversationMessage $conversationMessage): self
    {
        $this->queryBuilder
            ->andWhere('message = :conversationMessage')
            ->setParameter('conversationMessage', $conversationMessage);

        return $this;
    }

    public function orderByMessageCreatedAt(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder
            ->orderBy('message.createdAt', $order)
            ->addOrderBy('message.id', $order);

        return $this;
    }
}
