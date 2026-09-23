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

namespace App\Domain\ArticleCategory\Repository;

use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ArticleCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCategory::class);
    }

    private function createArticleCategoryQueryBuilder(): ArticleCategoryQueryBuilder
    {
        return new ArticleCategoryQueryBuilder($this->createQueryBuilder('article_category'));
    }

    public function allByRoles(iterable $roles): QueryBuilder
    {
        return $this->createArticleCategoryQueryBuilder()
            ->distinct()
            ->whereByRoles($roles)
            ->whereByActive(true)
            ->getQueryBuilder();
    }
}
