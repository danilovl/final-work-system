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

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class ConversationQueryBuilder extends BaseQueryBuilder
{
    public function selectRelations(): self
    {
        $this->queryBuilder->addSelect('
            messages, 
            type, 
            work, 
            work_status, 
            work_type, 
            participants, 
            participantsUser, 
            messagesOwner
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

    public function leftJoinMessagesOwner(): self
    {
        $this->queryBuilder->leftJoin('messages.owner', 'messagesOwner');

        return $this;
    }

    /**
     * @param int[] $ids
     */
    public function byIds(array $ids): self
    {
        $this->queryBuilder
            ->andWhere('conversation.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $this;
    }

    public function byWorkAndParticipantUser(Work $work, User $user): self
    {
        $this->queryBuilder
            ->andWhere('conversation.work = :work')
            ->andWhere('participants.user = :user')
            ->setParameter('work', $work)
            ->setParameter('user', $user);

        return $this;
    }

    public function orderByMessagesCreatedAt(string $order = Order::Descending->value): self
    {
        $this->queryBuilder->orderBy('messages.createdAt', $order);

        return $this;
    }

    public function setMaxResultsOne(): self
    {
        $this->queryBuilder->setMaxResults(1);

        return $this;
    }
}
