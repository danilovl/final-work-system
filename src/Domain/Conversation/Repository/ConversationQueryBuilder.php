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

namespace App\Domain\Conversation\Repository;

use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class ConversationQueryBuilder extends BaseQueryBuilder
{
    public function selectRelations(): self
    {
        $this->queryBuilder->addSelect('
            type, 
            work, 
            work_status, 
            work_type, 
            participants, 
            participantsUser
        ');

        return $this;
    }

    public function joinType(): self
    {
        $this->queryBuilder->join('conversation.type', 'type');

        return $this;
    }

    public function leftJoinWork(): self
    {
        $this->queryBuilder->leftJoin('conversation.work', 'work');

        return $this;
    }

    public function leftJoinWorkStatus(): self
    {
        $this->queryBuilder->leftJoin('work.status', 'work_status');

        return $this;
    }

    public function leftJoinWorkType(): self
    {
        $this->queryBuilder->leftJoin('work.type', 'work_type');

        return $this;
    }

    public function leftJoinParticipants(): self
    {
        $this->queryBuilder->leftJoin('conversation.participants', 'participants');

        return $this;
    }

    public function leftJoinParticipantsUser(): self
    {
        $this->queryBuilder->leftJoin('participants.user', 'participantsUser');

        return $this;
    }

    public function leftJoinMessages(): self
    {
        $this->queryBuilder->leftJoin('conversation.messages', 'messages');

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function whereByIds(array $ids): self
    {
        $this->queryBuilder
            ->andWhere('conversation.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $this;
    }

    /**
     * @param int[] $workIds
     */
    public function whereByWorkIds(array $workIds): self
    {
        $this->queryBuilder
            ->andWhere('conversation.work IN (:workIds)')
            ->setParameter('workIds', $workIds);

        return $this;
    }

    public function whereByWorkAndParticipantUser(Work $work, User $user): self
    {
        $this->queryBuilder
            ->andWhere('conversation.work = :work')
            ->andWhere('participants.user = :user')
            ->setParameter('work', $work)
            ->setParameter('user', $user);

        return $this;
    }

    public function orderByLastMessageCreatedAt(SortDirection $order = SortDirection::Descending): self
    {
        $lastMessageCreatedAt = sprintf(
            '(SELECT MAX(last_message.createdAt) FROM %s last_message WHERE last_message.conversation = conversation) AS HIDDEN lastMessageCreatedAt',
            ConversationMessage::class
        );

        $lastMessageId = sprintf(
            '(SELECT MAX(last_message_id.id) FROM %1$s last_message_id WHERE last_message_id.conversation = conversation AND last_message_id.createdAt = (SELECT MAX(last_message_date.createdAt) FROM %1$s last_message_date WHERE last_message_date.conversation = conversation)) AS HIDDEN lastMessageId',
            ConversationMessage::class
        );

        $this->queryBuilder
            ->addSelect($lastMessageCreatedAt)
            ->addSelect($lastMessageId)
            ->orderBy('lastMessageCreatedAt', $order)
            ->addOrderBy('lastMessageId', $order)
            ->addOrderBy('conversation.id', $order);

        return $this;
    }

    public function orderByMessagesCreatedAt(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder
            ->orderBy('messages.createdAt', $order)
            ->addOrderBy('messages.id', $order);

        return $this;
    }

    public function setMaxResultsOne(): self
    {
        $this->queryBuilder->setMaxResults(1);

        return $this;
    }
}
