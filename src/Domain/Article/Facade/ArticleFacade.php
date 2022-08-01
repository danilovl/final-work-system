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

namespace App\Domain\Article\Facade;

use App\Domain\Article\Repository\ArticleRepository;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Doctrine\ORM\Query;

class ArticleFacade
{
    public function __construct(private readonly ArticleRepository $articleRepository) {}

    public function queryArticlesByCategory(ArticleCategory $articleCategory): Query
    {
        return $this->articleRepository
            ->allByArticleCategory($articleCategory)
            ->getQuery();
    }
}
