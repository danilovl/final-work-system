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

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetArticleCategoryArticleListQuery implements QueryInterface
{
    private function __construct(
        public Request $request,
        public ArticleCategory $articleCategory
    ) {}

    public static function create(Request $request, ArticleCategory $articleCategory): self
    {
        return new self($request, $articleCategory);
    }
}
