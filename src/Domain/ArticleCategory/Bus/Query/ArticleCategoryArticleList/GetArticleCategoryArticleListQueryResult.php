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

namespace App\Domain\ArticleCategory\Bus\Query\ArticleCategoryArticleList;

use App\Domain\Article\Entity\Article;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetArticleCategoryArticleListQueryResult
{
    /**
     * @param PaginationInterface<int, Article> $articles
     */
    public function __construct(public PaginationInterface $articles) {}
}
