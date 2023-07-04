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

namespace App\Domain\ArticleCategory\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use App\Domain\ArticleCategory\Http\{
    ArticleCategoryArticleListHandle
};
use App\Domain\ArticleCategory\Http\ArticleCategoryListHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleCategoryController extends AbstractController
{
    public function __construct(
        private readonly ArticleCategoryListHandle $articleCategoryListHandle,
        private readonly ArticleCategoryArticleListHandle $articleCategoryArticleListHandle
    ) {}

    public function list(Request $request): Response
    {
        return $this->articleCategoryListHandle->handle($request);
    }

    public function articleList(Request $request, ArticleCategory $articleCategory): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $articleCategory);

        return $this->articleCategoryArticleListHandle->handle($request, $articleCategory);
    }
}
