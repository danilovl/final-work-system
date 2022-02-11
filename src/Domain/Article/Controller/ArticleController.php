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

namespace App\Domain\Article\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Http\ArticleDetailHandle;
use App\Domain\Article\Security\Voter\Subject\ArticleVoterSubject;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    public function __construct(private ArticleDetailHandle $articleDetailHandle)
    {
    }

    #[ParamConverter('article', class: Article::class, options: ['id' => 'id_article'])]
    #[ParamConverter('articleCategory', class: ArticleCategory::class, options: ['id' => 'id_category'])]
    public function detail(Article $article, ArticleCategory $articleCategory): Response
    {
        $articleVoterSubject = (new ArticleVoterSubject())
            ->setArticle($article)
            ->setArticleCategory($articleCategory);

        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $articleVoterSubject);

        return $this->articleDetailHandle->handle($article, $articleCategory);
    }
}
