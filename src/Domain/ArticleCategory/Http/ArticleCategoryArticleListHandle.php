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
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Infrastructure\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\ArticleCategory\Bus\Query\ArticleCategoryArticleList\{
    GetArticleCategoryArticleListQuery,
    GetArticleCategoryArticleListQueryResult
};
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ArticleCategoryArticleListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, ArticleCategory $articleCategory): Response
    {
        $query = GetArticleCategoryArticleListQuery::create($request, $articleCategory);
        /** @var GetArticleCategoryArticleListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $this->seoPageService
            ->setTitle('app.page.article_list')
            ->addTitle($articleCategory->getName(), SeoPageConstant::VERTICAL_SEPARATOR->value);

        return $this->twigRenderService->renderToResponse('domain/article_category/article_list.html.twig', [
            'articleCategory' => $articleCategory,
            'articles' => $result->articles
        ]);
    }
}
