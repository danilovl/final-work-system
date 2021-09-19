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

use App\Constant\VoterSupportConstant;
use App\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class ArticleCategoryController extends BaseController
{
    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.article_category.list')->handle($request);
    }

    public function articleList(Request $request, ArticleCategory $articleCategory): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleCategory);

        return $this->get('app.http_handle.article_category.article_list')->handle($request, $articleCategory);
    }
}
