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

namespace App\Domain\Article\Http;

use App\Application\Service\{
    TwigRenderService
};
use App\Application\Service\SeoPageService;
use App\Domain\Article\Entity\Article;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use Symfony\Component\HttpFoundation\Response;

readonly class ArticleDetailHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService
    ) {}

    public function handle(Article $article, ArticleCategory $articleCategory): Response
    {
        $this->seoPageService->setTitle($article->getTitle());

        return $this->twigRenderService->render('article/detail.html.twig', [
            'article' => $article,
            'articleCategory' => $articleCategory
        ]);
    }
}
