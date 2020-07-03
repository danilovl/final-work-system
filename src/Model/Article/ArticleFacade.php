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

namespace App\Model\Article;

use Doctrine\ORM\Query;
use App\Entity\ArticleCategory;
use App\Repository\ArticleRepository;

class ArticleFacade
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function queryArticlesByCategory(ArticleCategory $articleCategory): Query
    {
        return $this->articleRepository
            ->allByArticleCategory($articleCategory)
            ->getQuery();
    }
}
