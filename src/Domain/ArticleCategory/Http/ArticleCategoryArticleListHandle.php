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

use App\Application\Constant\SeoPageConstant;
use App\Application\Service\{
    SeoPageService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\Article\Facade\ArticleFacade;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ArticleCategoryArticleListHandle
{
    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly ArticleFacade $articleFacade,
        private readonly PaginatorService $paginatorService,
        private readonly SeoPageService $seoPageService
    ) {}

    public function handle(Request $request, ArticleCategory $articleCategory): Response
    {
        $articlesQuery = $this->articleFacade
            ->queryArticlesByCategory($articleCategory);

        $articles = $this->paginatorService
            ->createPaginationRequest($request, $articlesQuery);

        $this->seoPageService
            ->setTitle('app.page.article_list')
            ->addTitle($articleCategory->getName(), SeoPageConstant::VERTICAL_SEPARATOR);

        return $this->twigRenderService->render('article_category/article_list.html.twig', [
            'articles' => $articles,
            'articleCategory' => $articleCategory
        ]);
    }
}
