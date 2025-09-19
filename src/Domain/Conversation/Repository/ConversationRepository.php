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

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Webmozart\Assert\Assert;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation');
    }

    public function allByParticipantUser(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->addSelect('messages, type, work, participants, participantsUser, messagesOwner')
            ->join('conversation.type', 'type')
            ->leftJoin('conversation.work', 'work')
            ->leftJoin('conversation.participants', 'participants')
            ->leftJoin('participants.user', 'participantsUser')
            ->leftJoin('conversation.messages', 'messages')
            ->leftJoin('messages.owner', 'messagesOwner')
            ->where('participants.user = :user')
            ->orderBy('messages.createdAt', Order::Descending->value)
            ->setParameter('user', $user);
    }

    /**
     * @param int[] $ids
     */
    public function allByIds(array $ids): QueryBuilder
    {
        Assert::allInteger($ids);

        return $this->baseQueryBuilder()
            ->addSelect('messages, type, work, participants, participantsUser, messagesOwner')
            ->join('conversation.type', 'type')
            ->leftJoin('conversation.work', 'work')
            ->leftJoin('conversation.participants', 'participants')
            ->leftJoin('participants.user', 'participantsUser')
            ->leftJoin('conversation.messages', 'messages')
            ->leftJoin('messages.owner', 'messagesOwner')
            ->where('conversation.id IN (:ids)')
            ->orderBy('messages.createdAt', Order::Descending->value)
            ->setParameter('ids', $ids);
    }

    /**
     * @param ConversationType[] $types
     */
    public function addFilterByTypes(QueryBuilder $queryBuilder, array $types): QueryBuilder
    {
        Assert::allIsInstanceOf($types, ConversationType::class);

        $ids = array_map(static fn (ConversationType $type): int => $type->getId(), $types);
        if (count($ids) === 0) {
            return $queryBuilder;
        }

        return $queryBuilder
            ->andWhere('type.id IN (:typeIds)')
            ->setParameter('typeIds', $ids);
    }
}
