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

namespace FinalWork\FinalWorkBundle\Model\Article;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\{
    ArticleCategory,
    Repository\ArticleCategoryRepository
};

class ArticleCategoryFacade
{
    /**
     * @var ArticleCategoryRepository
     */
    private $articleCategoryRepository;

    /**
     * ArticleCategoryFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->articleCategoryRepository = $entityManager->getRepository(ArticleCategory::class);
    }

    /**
     * @param iterable $roles
     * @return Query
     */
    public function queryCategoriesByRoles(iterable $roles): Query
    {
        return $this->articleCategoryRepository
            ->findAllByRoles($roles)
            ->getQuery();
    }
}
