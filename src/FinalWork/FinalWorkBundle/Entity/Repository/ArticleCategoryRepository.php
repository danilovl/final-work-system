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

class ArticleCategoryRepository extends EntityRepository
{
    /**
     * @param iterable $roles
     * @return QueryBuilder
     */
    public function findAllByRoles(iterable $roles): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('article_category')
            ->distinct();

        foreach ($roles as $key => $role) {
            if ($key === 0) {
                $queryBuilder->andWhere("article_category.access LIKE :value_$key");
            } else {
                $queryBuilder->orWhere("article_category.access LIKE :value_$key");
            }
            $queryBuilder->setParameter("value_$key", '%' . $role . '%');
        }

        $queryBuilder->andWhere('article_category.active = :active')
            ->setParameter('active', true)
            ->setCacheable(true);

        return $queryBuilder;
    }
}