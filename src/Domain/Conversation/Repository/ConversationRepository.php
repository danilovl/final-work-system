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
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Webmozart\Assert\Assert;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    private function createConversationQueryBuilder(): ConversationQueryBuilder
    {
        return new ConversationQueryBuilder($this->createQueryBuilder('conversation'));
    }

    public function allByParticipantUser(User $user): QueryBuilder
    {
        $sub = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('1')
            ->from(ConversationParticipant::class, 'p')
            ->where('p.conversation = conversation')
            ->andWhere('p.user = :user');

        $callback = static function (QueryBuilder $qb) use ($sub, $user): void {
            $qb
                ->where($qb->expr()->exists($sub->getDQL()))
                ->setParameter('user', $user);
        };

        return $this->createConversationQueryBuilder()
            ->selectRelations()
            ->joinType()
            ->leftJoinWork()
            ->leftJoinWorkStatus()
            ->leftJoinWorkType()
            ->leftJoinParticipants()
            ->leftJoinParticipantsUser()
            ->leftJoinMessages()
            ->leftJoinMessagesOwner()
            ->orderByMessagesCreatedAt()
            ->byCallback($callback)
            ->getQueryBuilder();
    }

    /**
     * @param int[] $ids
     */
    public function allByIds(array $ids): QueryBuilder
    {
        Assert::allInteger($ids);

        return $this->createConversationQueryBuilder()
            ->selectRelations()
            ->joinType()
            ->leftJoinWork()
            ->leftJoinWorkStatus()
            ->leftJoinWorkType()
            ->leftJoinParticipants()
            ->leftJoinParticipantsUser()
            ->leftJoinMessages()
            ->leftJoinMessagesOwner()
            ->whereByIds($ids)
            ->orderByMessagesCreatedAt()
            ->getQueryBuilder();
    }

    public function oneByWorkUser(
        Work $work,
        User $user
    ): QueryBuilder {
        return $this->createConversationQueryBuilder()
            ->selectRelations()
            ->joinType()
            ->leftJoinWork()
            ->leftJoinWorkStatus()
            ->leftJoinWorkType()
            ->leftJoinParticipants()
            ->leftJoinParticipantsUser()
            ->leftJoinMessages()
            ->leftJoinMessagesOwner()
            ->whereByWorkAndParticipantUser($work, $user)
            ->orderByMessagesCreatedAt()
            ->setMaxResultsOne()
            ->getQueryBuilder();
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
