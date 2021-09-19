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
use App\Entity\{
    Article,
    ArticleCategory
};
use App\Security\Voter\Subject\ArticleVoterSubject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends BaseController
{
    /**
     * @ParamConverter("article", class="App\Entity\Article", options={"id" = "id_article"})
     * @ParamConverter("articleCategory", class="App\Entity\ArticleCategory", options={"id" = "id_category"})
     */
    public function detail(Article $article, ArticleCategory $articleCategory): Response
    {
        $articleVoterSubject = (new ArticleVoterSubject)
            ->setArticle($article)
            ->setArticleCategory($articleCategory);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleVoterSubject);

        return $this->get('app.http_handle.article.detail')->handle($article, $articleCategory);
    }
}
