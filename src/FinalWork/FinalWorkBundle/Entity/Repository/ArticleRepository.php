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
use FinalWork\FinalWorkBundle\Entity\ArticleCategory;

class ArticleRepository extends EntityRepository
{
    /**
     * @param ArticleCategory $articleCategory
     * @return QueryBuilder
     */
    public function findAllByArticleCategory(ArticleCategory $articleCategory): QueryBuilder
    {
        return $this->createQueryBuilder('article')
            ->distinct()
            ->innerJoin('article.categories', 'categories')
            ->where('article.active = :active')
            ->andWhere('categories = :articleCategory')
            ->orderBy('article.createdAt', Criteria::DESC)
            ->setParameter('active', true)
            ->setParameter('articleCategory', $articleCategory)
            ->setCacheable(true);;
    }
}