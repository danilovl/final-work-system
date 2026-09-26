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
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Webmozart\Assert\Assert;

class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }

    private function createConversationMessageQueryBuilder(): ConversationMessageQueryBuilder
    {
        return new ConversationMessageQueryBuilder($this->baseQueryBuilder());
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation_message');
    }

    public function allByWorkUser(Work $work, User $user): QueryBuilder
    {
        return $this->createConversationMessageQueryBuilder()
            ->innerJoinConversation()
            ->leftJoinConversationParticipants()
            ->whereByConversationWork($work)
            ->whereByParticipantsUser($user)
            ->orderByCreatedAt(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function countMessageByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createConversationMessageQueryBuilder()
            ->selectCountId()
            ->leftJoinStatuses()
            ->whereByStatusType($statusType)
            ->whereByStatusUser($user)
            ->whereByStatusMessageNotNull()
            ->whereByStatusConversationNotNull()
            ->getQueryBuilder();
    }

    public function allByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createConversationMessageQueryBuilder()
            ->leftJoinStatuses()
            ->whereByStatusType($statusType)
            ->whereByStatusUser($user)
            ->whereByStatusMessageNotNull()
            ->whereByStatusConversationNotNull()
            ->orderByCreatedAt(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function allByConversation(Conversation $conversation, ?int $limit = null): QueryBuilder
    {
        $builder = $this->createConversationMessageQueryBuilder()
            ->selectOwner()
            ->selectConversation()
            ->joinOwner()
            ->joinConversation()
            ->whereByConversation($conversation)
            ->orderByCreatedAt(Order::Descending->value);

        if ($limit !== null) {
            $builder = $builder->setMaxResults($limit);
        }

        return $builder->getQueryBuilder();
    }

    /**
     * @param int[] $ids
     */
    public function byIds(array $ids): QueryBuilder
    {
        Assert::allInteger($ids);

        return $this->createConversationMessageQueryBuilder()
            ->selectOwner()
            ->selectConversation()
            ->joinOwner()
            ->joinConversation()
            ->whereByIds($ids)
            ->orderByCreatedAt(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function countMessagesByUserStatus(
        User $user,
        ConversationMessageStatusType $statusType
    ): QueryBuilder {
        return $this->createConversationMessageQueryBuilder()
            ->distinct()
            ->selectCountId()
            ->leftJoinStatuses()
            ->whereByStatusType($statusType)
            ->whereByStatusUser($user)
            ->whereByStatusMessageNotNull()
            ->whereByStatusConversationNotNull()
            ->orderByCreatedAt(Order::Descending->value)
            ->getQueryBuilder();
    }

    public function allByConversationAfterDate(
        Conversation $conversation,
        DateTimeImmutable $date
    ): QueryBuilder {
        return $this->createConversationMessageQueryBuilder()
            ->selectMessageOnly()
            ->whereByConversation($conversation)
            ->whereByCreatedAfter($date)
            ->orderByCreatedAt(Order::Descending->value)
            ->getQueryBuilder();
    }
}
