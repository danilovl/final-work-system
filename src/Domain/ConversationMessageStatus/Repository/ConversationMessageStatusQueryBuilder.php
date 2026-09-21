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
use Doctrine\Common\Collections\Order;

class ConversationMessageStatusQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinMessage(): self
    {
        $this->queryBuilder->leftJoin('conversation_message_status.message', 'message');

        return $this;
    }

    public function byUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.user = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function byConversation(Conversation $conversation): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->setParameter('conversation', $conversation);

        return $this;
    }

    public function byType(ConversationMessageStatusType $type): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message_status.type = :type')
            ->setParameter('type', $type);

        return $this;
    }

    public function byMessage(ConversationMessage $conversationMessage): self
    {
        $this->queryBuilder
            ->andWhere('message = :conversationMessage')
            ->setParameter('conversationMessage', $conversationMessage);

        return $this;
    }

    public function orderByMessageCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('message.createdAt', $order);

        return $this;
    }
}
