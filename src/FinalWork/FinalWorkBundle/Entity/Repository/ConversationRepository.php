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

class ConversationRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('conversation')
            ->setCacheable(true);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByUser(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->addSelect('participants')
            ->addSelect('messages')
            ->leftJoin('conversation.participants', 'participants')
            ->leftJoin('conversation.messages', 'messages')
            ->where('participants.user = :user')
            ->orWhere('conversation.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('messages.createdAt', Criteria::DESC);
    }
}
