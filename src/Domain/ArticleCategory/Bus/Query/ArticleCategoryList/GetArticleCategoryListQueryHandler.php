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

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\Article\Facade\ArticleCategoryFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetArticleCategoryListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ArticleCategoryFacade $articleCategoryFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetArticleCategoryListQuery $query): GetArticleCategoryListQueryResult
    {
        $articleCategoriesQuery = $this->articleCategoryFacade->queryCategoriesByRoles($query->roles);
        $pagination = $this->paginatorService->createPaginationRequest($query->request, $articleCategoriesQuery);

        return new GetArticleCategoryListQueryResult($pagination);
    }
}
