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

namespace App\Model\Article\Controller;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Model\Article\Entity\Article;
use App\Model\Article\Http\ArticleDetailHandle;
use App\Model\Article\Security\Voter\Subject\ArticleVoterSubject;
use App\Model\ArticleCategory\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends BaseController
{
    public function __construct(private ArticleDetailHandle $articleDetailHandle)
    {
    }

    /**
     * @ParamConverter("article", class="App\Model\Article\Entity\Article", options={"id" = "id_article"})
     * @ParamConverter("articleCategory", class="App\Model\ArticleCategory\Entity\ArticleCategory", options={"id" = "id_category"})
     */
    public function detail(Article $article, ArticleCategory $articleCategory): Response
    {
        $articleVoterSubject = (new ArticleVoterSubject())
            ->setArticle($article)
            ->setArticleCategory($articleCategory);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleVoterSubject);

        return $this->articleDetailHandle->handle($article, $articleCategory);
    }
}
