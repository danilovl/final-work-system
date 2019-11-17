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

class MediaCategoryRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media_category')
            ->setCacheable(true);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('media_category.owner = :user')
            ->orderBy('media_category.name', Criteria::ASC)
            ->setParameter('user', $user);
    }

    /**
     * @param iterable $users
     * @return QueryBuilder
     */
    public function findAllByOwners(iterable $users): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('media_category.owner IN(:users)')
            ->orderBy('media_category.createdAt', Criteria::DESC)
            ->setParameter('users', $users);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByMediaOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->leftJoin('media_category.medias', 'medias')
            ->where('medias.owner = :user')
            ->orderBy('media_category.createdAt', Criteria::DESC)
            ->setParameter('user', $user);
    }

    /**
     * @param iterable $users
     * @return QueryBuilder
     */
    public function findAllByMediaOwners(iterable $users): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->leftJoin('media_category.medias', 'medias')
            ->where('medias.owner IN(:users)')
            ->orderBy('media_category.createdAt', Criteria::DESC)
            ->setParameter('users', $users);
    }
}
