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

namespace App\Model\ArticleCategory\Controller;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\ArticleCategory\Entity\ArticleCategory;
use App\Model\ArticleCategory\Http\{
    ArticleCategoryListHandle,
    ArticleCategoryArticleListHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ArticleCategoryController extends BaseController
{
    public function __construct(
        private ArticleCategoryListHandle $articleCategoryListHandle,
        private ArticleCategoryArticleListHandle $articleCategoryArticleListHandle
    ) {
    }

    public function list(Request $request): Response
    {
        return $this->articleCategoryListHandle->handle($request);
    }

    public function articleList(Request $request, ArticleCategory $articleCategory): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleCategory);

        return $this->articleCategoryArticleListHandle->handle($request, $articleCategory);
    }
}
