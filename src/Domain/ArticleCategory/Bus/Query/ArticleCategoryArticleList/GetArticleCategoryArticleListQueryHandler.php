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

use App\Infrastructure\Service\PaginatorService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Application\Interfaces\Bus\{QueryBusInterface, QueryHandlerInterface};
use App\Domain\Article\Bus\Query\ArticleQueryByCategory\{GetArticleQueryByCategoryQuery, GetArticleQueryByCategoryQueryResult};

#[AsMessageHandler]
readonly class GetArticleCategoryArticleListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private PaginatorService $paginatorService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(GetArticleCategoryArticleListQuery $query): GetArticleCategoryArticleListQueryResult
    {
        $getArticleQueryByCategoryQuery = GetArticleQueryByCategoryQuery::create($query->articleCategory);
        /** @var GetArticleQueryByCategoryQueryResult $getArticleQueryByCategoryQueryResult */
        $getArticleQueryByCategoryQueryResult = $this->queryBus->handle($getArticleQueryByCategoryQuery);
        $articlesQuery = $getArticleQueryByCategoryQueryResult->query;
        $articles = $this->paginatorService->createPaginationRequest($query->request, $articlesQuery);

        return new GetArticleCategoryArticleListQueryResult($articles);
    }
}
