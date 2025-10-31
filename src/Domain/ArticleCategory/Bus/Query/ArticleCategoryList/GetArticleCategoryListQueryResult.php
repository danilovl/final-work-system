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

namespace App\Domain\ArticleCategory\Bus\Query\ArticleCategoryList;

use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetArticleCategoryListQueryResult
{
    /**
     * @param PaginationInterface<int, ArticleCategory> $articleCategories
     */
    public function __construct(public PaginationInterface $articleCategories) {}
}
