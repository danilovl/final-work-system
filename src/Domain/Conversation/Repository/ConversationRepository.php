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

namespace App\Domain\Conversation\Repository;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation')
            ->setCacheable(true);
    }

    public function allByParticipantUser(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->addSelect('messages')
            ->leftJoin('conversation.participants', 'participants')
            ->leftJoin('conversation.messages', 'messages')
            ->where('participants.user = :user')
            ->orderBy('messages.createdAt', Criteria::DESC)
            ->setParameter('user', $user);
    }

    public function allByIds(array $ids): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->addSelect('participants')
            ->addSelect('messages')
            ->leftJoin('conversation.participants', 'participants')
            ->leftJoin('conversation.messages', 'messages')
            ->where('conversation.id IN (:ids)')
            ->orderBy('messages.createdAt', Criteria::DESC)
            ->setParameter('ids', $ids);
    }

    /**
     * @param ConversationType[] $types
     */
    public function addFilterByTypes(QueryBuilder $queryBuilder, array $types): QueryBuilder
    {
        $ids = array_map(static fn(ConversationType $type): int => $type->getId(), $types);
        if (count($ids) === 0) {
            return $queryBuilder;
        }

        return $queryBuilder
            ->join('conversation.type', 'type')
            ->andWhere('type.id IN (:typeIds)')
            ->setParameter('typeIds', $ids);
    }
}
