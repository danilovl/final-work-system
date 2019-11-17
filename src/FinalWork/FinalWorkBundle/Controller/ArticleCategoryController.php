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

namespace FinalWork\FinalWorkBundle\Controller;

use FinalWork\FinalWorkBundle\Constant\{
    SeoPageConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Entity\ArticleCategory;
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleCategoryController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     * @throws LogicException
     */
    public function listAction(Request $request): Response
    {
        $articleCategoriesQuery = $this->get('final_work.facade.article_category')
            ->queryCategoriesByRoles($this->getUser()->getRoles());

        $this->get('final_work.seo_page')->setTitle('finalwork.page.article_category_list');

        return $this->render('@FinalWork/article_category/list.html.twig', [
            'articleCategories' => $this->createPagination($request, $articleCategoriesQuery)
        ]);
    }

    /**
     * @param Request $request
     * @param ArticleCategory $articleCategory
     * @return Response
     *
     * @throws \Symfony\Component\Finder\Exception\AccessDeniedException
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function articleListAction(
        Request $request,
        ArticleCategory $articleCategory
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleCategory);

        $articlesQuery = $this->get('final_work.facade.article')
            ->getArticlesQueryByCategory($articleCategory);

        $this->get('final_work.seo_page')
            ->setTitle('finalwork.page.article_list')
            ->addTitle($articleCategory->getName(), SeoPageConstant::VERTICAL_SEPARATOR);

        return $this->render('@FinalWork/article_category/article_list.html.twig', [
            'articles' => $this->createPagination($request, $articlesQuery),
            'articleCategory' => $articleCategory
        ]);
    }
}
