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
use FinalWork\SonataUserBundle\Entity\User;

class EventScheduleRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event_schedule')
            ->setCacheable(true);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('event_schedule.owner = :user')
            ->setParameter('user', $user);
    }
}
