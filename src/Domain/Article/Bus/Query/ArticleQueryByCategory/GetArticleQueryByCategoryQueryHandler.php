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

namespace App\Domain\Article\Bus\Query\ArticleQueryByCategory;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Article\Facade\ArticleFacade;

readonly class GetArticleQueryByCategoryQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ArticleFacade $articleFacade) {}

    public function __invoke(GetArticleQueryByCategoryQuery $query): GetArticleQueryByCategoryQueryResult
    {
        $query = $this->articleFacade->queryAllByCategory($query->articleCategory);

        return new GetArticleQueryByCategoryQueryResult($query);
    }
}
