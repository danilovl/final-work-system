<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Controller;

use App\Constant\{
    SeoPageConstant,
    VoterSupportConstant
};
use App\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ArticleCategoryController extends BaseController
{
    public function list(Request $request): Response
    {
        $articleCategoriesQuery = $this->get('app.facade.article_category')
            ->queryCategoriesByRoles($this->getUser()->getRoles());

        return $this->render('article_category/list.html.twig', [
            'articleCategories' => $this->createPagination($request, $articleCategoriesQuery)
        ]);
    }

    public function articleList(
        Request $request,
        ArticleCategory $articleCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleCategory);

        $articlesQuery = $this->get('app.facade.article')
            ->queryArticlesByCategory($articleCategory);

        $this->get('app.seo_page')
            ->setTitle('app.page.article_list')
            ->addTitle($articleCategory->getName(), SeoPageConstant::VERTICAL_SEPARATOR);

        return $this->render('article_category/article_list.html.twig', [
            'articles' => $this->createPagination($request, $articlesQuery),
            'articleCategory' => $articleCategory
        ]);
    }
}
