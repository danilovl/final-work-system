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
use App\Application\Service\AuthorizationCheckerService;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use App\Domain\ArticleCategory\Http\{
    ArticleCategoryArticleListHandle
};
use App\Domain\ArticleCategory\Http\ArticleCategoryListHandle;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ArticleCategoryController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ArticleCategoryListHandle $articleCategoryListHandle,
        private ArticleCategoryArticleListHandle $articleCategoryArticleListHandle
    ) {}

    public function list(Request $request): Response
    {
        return $this->articleCategoryListHandle->handle($request);
    }

    public function articleList(Request $request, ArticleCategory $articleCategory): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $articleCategory);

        return $this->articleCategoryArticleListHandle->handle($request, $articleCategory);
    }
}
