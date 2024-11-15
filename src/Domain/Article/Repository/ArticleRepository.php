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

namespace App\Domain\Article\Repository;

use App\Domain\Article\Entity\Article;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function allByArticleCategory(ArticleCategory $articleCategory): QueryBuilder
    {
        return $this->createQueryBuilder('article')
            ->distinct()
            ->innerJoin('article.categories', 'categories')
            ->where('article.active = :active')
            ->andWhere('categories = :articleCategory')
            ->orderBy('article.createdAt', Order::Descending->value)
            ->setParameter('active', true)
            ->setParameter('articleCategory', $articleCategory);
    }
}
