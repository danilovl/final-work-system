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

namespace App\Domain\ConversationMessage\Repository;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message');
    }

    public function allByWorkUser(
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
            ->orderBy('conversation_message.createdAt', Order::Descending->value);
    }

    public function countMessageByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->select('count(conversation_message.id)')
            ->leftJoin('conversation_message.statuses', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }

    public function allByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->leftJoin('conversation_message.statuses', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->orderBy('conversation_message.createdAt', Order::Descending->value)
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }

    public function allByConversation(Conversation $conversation, int $limit = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('conversation_message')
            ->addSelect('owner, conversation')
            ->join('conversation_message.owner', 'owner')
            ->join('conversation_message.conversation', 'conversation')
            ->where('conversation_message.conversation = :conversation')
            ->orderBy('conversation_message.createdAt', Order::Descending->value)
            ->setParameter('conversation', $conversation);

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }

    public function byIds(array $ids): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message')
            ->addSelect('owner, conversation')
            ->join('conversation_message.owner', 'owner')
            ->join('conversation_message.conversation', 'conversation')
            ->where('conversation_message.id IN (:ids)')
            ->orderBy('conversation_message.createdAt', Order::Descending->value)
            ->setParameter('ids', $ids);
    }

    public function countMessagesByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->distinct()
            ->select('count(conversation_message.id)')
            ->leftJoin('conversation_message.statuses', 'status')
            ->where('status.type = :type')
            ->andWhere('status.user = :user')
            ->andWhere('status.message IS NOT NULL')
            ->andWhere('status.conversation IS NOT NULL')
            ->orderBy('conversation_message.createdAt', Order::Descending->value)
            ->setParameter('user', $user)
            ->setParameter('type', $statusType);
    }

    public function allByConversationAfterDate(
        Conversation $conversation,
        DateTime $date
    ): QueryBuilder {
        return $this->createQueryBuilder('conversation_message')
            ->select('conversation_message')
            ->where('conversation_message.conversation = :conversation')
            ->andWhere('conversation_message.createdAt > :date')
            ->orderBy('conversation_message.createdAt', Order::Descending->value)
            ->setParameter('conversation', $conversation)
            ->setParameter('date', $date);
    }
}
