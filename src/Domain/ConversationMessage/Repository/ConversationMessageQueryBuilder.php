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

namespace App\Domain\ConversationMessage\Repository;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use DateTimeImmutable;
use Doctrine\Common\Collections\Order;
use Webmozart\Assert\Assert;

class ConversationMessageQueryBuilder extends BaseQueryBuilder
{
    public function innerJoinConversation(): self
    {
        $this->queryBuilder->innerJoin('conversation_message.conversation', 'conversation');

        return $this;
    }

    public function leftJoinConversationParticipants(): self
    {
        $this->queryBuilder->leftJoin('conversation.participants', 'participants');

        return $this;
    }

    public function whereByConversationWork(Work $work): self
    {
        $this->queryBuilder
            ->andWhere('conversation.work = :work')
            ->setParameter('work', $work);

        return $this;
    }

    public function whereByParticipantsUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('participants.user = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function orderByCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('conversation_message.createdAt', $order);

        return $this;
    }

    public function selectCountId(): self
    {
        $this->queryBuilder->select('count(conversation_message.id)');

        return $this;
    }

    public function leftJoinStatuses(): self
    {
        $this->queryBuilder->leftJoin('conversation_message.statuses', 'status');

        return $this;
    }

    public function whereByStatusType(ConversationMessageStatusType $statusType): self
    {
        $this->queryBuilder
            ->andWhere('status.type = :type')
            ->setParameter('type', $statusType);

        return $this;
    }

    public function whereByStatusUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('status.user = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function whereByStatusMessageNotNull(): self
    {
        $this->queryBuilder->andWhere('status.message IS NOT NULL');

        return $this;
    }

    public function whereByStatusConversationNotNull(): self
    {
        $this->queryBuilder->andWhere('status.conversation IS NOT NULL');

        return $this;
    }

    public function selectOwner(): self
    {
        $this->queryBuilder->addSelect('owner');

        return $this;
    }

    public function selectConversation(): self
    {
        $this->queryBuilder->addSelect('conversation');

        return $this;
    }

    public function joinOwner(): self
    {
        $this->queryBuilder->join('conversation_message.owner', 'owner');

        return $this;
    }

    public function joinConversation(): self
    {
        $this->queryBuilder->join('conversation_message.conversation', 'conversation');

        return $this;
    }

    public function whereByConversation(Conversation $conversation): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message.conversation = :conversation')
            ->setParameter('conversation', $conversation);

        return $this;
    }

    public function setMaxResults(int $maxResults): self
    {
        $this->queryBuilder->setMaxResults($maxResults);

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function whereByIds(array $ids): self
    {
        Assert::allInteger($ids);

        $this->queryBuilder
            ->andWhere('conversation_message.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $this;
    }

    public function distinct(): self
    {
        $this->queryBuilder->distinct();

        return $this;
    }

    public function selectMessageOnly(): self
    {
        $this->queryBuilder->select('conversation_message');

        return $this;
    }

    public function whereByCreatedAfter(DateTimeImmutable $date): self
    {
        $this->queryBuilder
            ->andWhere('conversation_message.createdAt > :date')
            ->setParameter('date', $date);

        return $this;
    }
}
