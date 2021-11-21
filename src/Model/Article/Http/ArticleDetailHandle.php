<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\Article\Http;

use App\Model\Article\Entity\Article;
use App\Model\ArticleCategory\Entity\ArticleCategory;
use App\Service\{
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\Response;

class ArticleDetailHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService
    ) {
    }

    public function handle(Article $article, ArticleCategory $articleCategory): Response
    {
        $this->seoPageService->setTitle($article->getTitle());

        return $this->twigRenderService->render('article/detail.html.twig', [
            'article' => $article,
            'articleCategory' => $articleCategory
        ]);
    }
}
