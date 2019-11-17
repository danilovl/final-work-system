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

use FinalWork\FinalWorkBundle\Constant\VoterSupportConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Article,
    ArticleCategory
};
use FinalWork\FinalWorkBundle\Security\Voter\Subject\ArticleVoterSubject;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArticleController extends BaseController
{
    /**
     * @param Article $article
     * @param ArticleCategory $articleCategory
     * @return Response
     *
     * @ParamConverter("article", class="FinalWork\FinalWorkBundle\Entity\Article", options={"id" = "id_article"})
     * @ParamConverter("articleCategory", class="FinalWork\FinalWorkBundle\Entity\ArticleCategory", options={"id" = "id_category"})
     *
     * @throws AccessDeniedException
     * @throws LogicException
     */
    public function detailAction(
        Article $article,
        ArticleCategory $articleCategory
    ): Response {
        $articleVoterSubject = (new ArticleVoterSubject)
            ->setArticle($article)
            ->setArticleCategory($articleCategory);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleVoterSubject);
        $this->get('final_work.seo_page')->setTitle($article->getTitle());

        return $this->render('@FinalWork/article/detail.html.twig', [
            'article' => $article,
            'articleCategory' => $articleCategory
        ]);
    }
}
