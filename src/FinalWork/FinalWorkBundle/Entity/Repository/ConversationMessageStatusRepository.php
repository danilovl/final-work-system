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
use FinalWork\FinalWorkBundle\Entity\{
    Conversation,
    ConversationMessage,
    ConversationMessageStatusType
};
use FinalWork\SonataUserBundle\Entity\User;

class ConversationMessageStatusRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message_status')
            ->leftJoin('conversation_message_status.message', 'message')
            ->setCacheable(true);
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByConversation(
        Conversation $conversation,
        User $user
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('conversation_message_status.user = :user')
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->orderBy('message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('conversation', $conversation);
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @param ConversationMessageStatusType $type
     * @return QueryBuilder
     */
    public function findOneByConversationUserType(
        Conversation $conversation,
        User $user,
        ConversationMessageStatusType $type
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('conversation_message_status.user = :user')
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->andWhere('conversation_message_status.type = :type')
            ->orderBy('message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('conversation', $conversation)
            ->setParameter('type', $type);
    }

    /**
     * @param Conversation $conversation
     * @param User $user
     * @return QueryBuilder
     */
    public function findOneByConversationUser(
        Conversation $conversation,
        User $user
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('conversation_message_status.user = :user')
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->orderBy('message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('conversation', $conversation);
    }

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     * @return QueryBuilder
     */
    public function findOneByMessageUser(
        ConversationMessage $conversationMessage,
        User $user
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('conversation_message_status.user = :user')
            ->andWhere('message = :conversationMessage')
            ->orderBy('message.createdAt', Criteria::DESC)
            ->setParameter('user', $user)
            ->setParameter('conversationMessage', $conversationMessage);
    }
}
