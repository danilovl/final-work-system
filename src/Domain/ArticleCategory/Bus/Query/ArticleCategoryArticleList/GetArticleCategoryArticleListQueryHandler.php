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

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\Article\Facade\ArticleFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetArticleCategoryArticleListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private PaginatorService $paginatorService,
        private ArticleFacade $articleFacade
    ) {}

    public function __invoke(GetArticleCategoryArticleListQuery $query): GetArticleCategoryArticleListQueryResult
    {
        $articlesQuery = $this->articleFacade->queryAllByCategory($query->articleCategory);
        $articles = $this->paginatorService->createPaginationRequest($query->request, $articlesQuery);

        return new GetArticleCategoryArticleListQueryResult($articles);
    }
}
