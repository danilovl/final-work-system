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
    Article,
    ArticleCategory
};
use FinalWork\FinalWorkBundle\Entity\Repository\ArticleRepository;

class ArticleFacade
{
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * ArticleFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->articleRepository = $entityManager->getRepository(Article::class);
    }

    /**
     * @param ArticleCategory $articleCategory
     * @return Query
     */
    public function queryArticlesByCategory(ArticleCategory $articleCategory): Query
    {
        return $this->articleRepository
            ->findAllByArticleCategory($articleCategory)
            ->getQuery();
    }
}
