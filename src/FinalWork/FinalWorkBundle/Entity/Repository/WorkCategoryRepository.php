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

class WorkCategoryRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllByOwner(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('work_category')
            ->addSelect('works')
            ->leftJoin('work_category.works', 'works')
            ->where('work_category.owner = :user')
            ->orderBy('work_category.name', Criteria::ASC)
            ->setParameter('user', $user);
    }
}
