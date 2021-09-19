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

namespace App\Model\ArticleCategory\Http;

use App\Model\Article\Facade\ArticleCategoryFacade;
use App\Service\{
    UserService,
    PaginatorService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ArticleCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private ArticleCategoryFacade $articleCategoryFacade,
        private PaginatorService $paginatorService
    ) {
    }

    public function handle(Request $request): Response
    {
        $articleCategoriesQuery = $this->articleCategoryFacade->queryCategoriesByRoles(
            $this->userService->getUser()->getRoles()
        );

        return $this->twigRenderService->render('article_category/list.html.twig', [
            'articleCategories' => $this->paginatorService->createPaginationRequest($request, $articleCategoriesQuery)
        ]);
    }
}