<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Conversation,
    ConversationMessageStatusType
};

class ConversationMessageRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message')
            ->setCacheable(true);
    }

    /**
     * @param Work $work
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByWorkUser(
        Work $work,
        User $user
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->innerJoin('conversation_message.conversation', 'conversation')
            ->leftJoin('conversation.participants', 'participants')
            ->where('conversation.work = :work')
            ->andWhere('participants.user = :user')
            ->setParameter('work', $work)
            ->setParameter('user', $user)
            ->orderBy('conversation_message.createdAt', Criteria::DESC);
    }

    /**
     * @param User $user
     * @param ConversationMessageStatusType $statusType
     * @return QueryBuilder
     */
    public function getCountMessageByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->select('count(conversation_message.id)')
            ->leftJoin('conversation_message.status', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }

    /**
     * @param User $user
     * @param ConversationMessageStatusType $statusType
     * @return QueryBuilder
     */
    public function findAllByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->leftJoin('conversation_message.status', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->orderBy('conversation_message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }

    /**
     * @param Conversation $conversation
     * @param int|null $limit
     * @return QueryBuilder
     */
    public function findAllByConversation(Conversation $conversation, int $limit = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('conversation_message')
            ->select('conversation_message')
            ->where('conversation_message.conversation = :conversation')
            ->orderBy('conversation_message.createdAt', Criteria::DESC)
            ->setParameter('conversation', $conversation);

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @param ConversationMessageStatusType $statusType
     * @return QueryBuilder
     */
    public function getCountMessagesByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->distinct()
            ->select('count(conversation_message.id)')
            ->leftJoin('conversation_message.status', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->orderBy('conversation_message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }
}
