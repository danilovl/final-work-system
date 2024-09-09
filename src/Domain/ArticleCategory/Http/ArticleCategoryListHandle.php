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

namespace App\Domain\ArticleCategory\Http;

use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\Article\Facade\ArticleCategoryFacade;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ArticleCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ArticleCategoryFacade $articleCategoryFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(Request $request): Response
    {
        $articleCategoriesQuery = $this->articleCategoryFacade->queryCategoriesByRoles(
            $this->userService->getUser()->getRoles()
        );

        return $this->twigRenderService->renderToResponse('domain/article_category/list.html.twig', [
            'articleCategories' => $this->paginatorService->createPaginationRequest($request, $articleCategoriesQuery)
        ]);
    }
}
