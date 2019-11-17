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

use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\FinalWorkBundle\Entity\Event;
use FinalWork\SonataUserBundle\Entity\User;

class CommentRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('comment')
            ->setCacheable(true);
    }

    /**
     * @param User $user
     * @param Event $event
     * @return QueryBuilder
     */
    public function findAllByOwnerEvent(
        User $user,
        Event $event
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('comment.event = :event')
            ->andWhere('comment.owner = :user')
            ->setParameter('user', $user)
            ->setParameter('event', $event);
    }
}
