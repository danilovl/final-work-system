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

namespace App\Model\ConversationMessageStatus\Repository;

use App\DataTransferObject\Repository\ConversationMessageStatusData;
use App\Model\Conversation\Entity\Conversation;
use App\Model\ConversationMessage\Entity\ConversationMessage;
use App\Model\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Model\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Model\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ConversationMessageStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessageStatus::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message_status')
            ->leftJoin('conversation_message_status.message', 'message')
            ->setCacheable(true);
    }

    public function allByConversation(
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

    public function oneByConversationUserType(ConversationMessageStatusData $data): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('conversation_message_status.user = :user')
            ->andWhere('conversation_message_status.conversation = :conversation')
            ->andWhere('conversation_message_status.type = :type')
            ->orderBy('message.createdAt', Criteria::DESC)
            ->setParameter('user', $data->user)
            ->setParameter('conversation', $data->conversation)
            ->setParameter('type', $data->type);
    }

    public function oneByConversationUser(
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

    public function oneByMessageUser(
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

    public function updateAllToStatus(
        User $user,
        ConversationMessageStatusType $type
    ): void {
        $this->createQueryBuilder('conversation_message_status')
            ->update()
            ->set('conversation_message_status.type', $type->getId())
            ->andWhere('conversation_message_status.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
