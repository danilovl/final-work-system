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
use App\Domain\ConversationMessageStatus\DTO\Repository\ConversationMessageStatusRepositoryDTO;
use App\Domain\ConversationMessageStatus\Entity\ConversationMessageStatus;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use SortDirection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ConversationMessageStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessageStatus::class);
    }

    private function createConversationMessageStatusQueryBuilder(): ConversationMessageStatusQueryBuilder
    {
        return new ConversationMessageStatusQueryBuilder($this->createQueryBuilder('conversation_message_status'));
    }

    public function allByConversation(
        Conversation $conversation,
        User $user
    ): QueryBuilder {
        return $this->createConversationMessageStatusQueryBuilder()
            ->leftJoinMessage()
            ->whereByUser($user)
            ->whereByConversation($conversation)
            ->orderByMessageCreatedAt(SortDirection::Descending)
            ->getQueryBuilder();
    }

    public function oneByConversationUserType(ConversationMessageStatusRepositoryDTO $data): QueryBuilder
    {
        return $this->createConversationMessageStatusQueryBuilder()
            ->leftJoinMessage()
            ->whereByUser($data->getUserNotNull())
            ->whereByConversation($data->getConversationNotNull())
            ->whereByType($data->getTypeNotNull())
            ->orderByMessageCreatedAt(SortDirection::Descending)
            ->getQueryBuilder();
    }

    public function oneByMessageUser(
        ConversationMessage $conversationMessage,
        User $user
    ): QueryBuilder {
        return $this->createConversationMessageStatusQueryBuilder()
            ->leftJoinMessage()
            ->whereByUser($user)
            ->whereByMessage($conversationMessage)
            ->orderByMessageCreatedAt(SortDirection::Descending)
            ->getQueryBuilder();
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
