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

namespace App\Domain\ArticleCategory\Http;

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\ArticleCategory\Bus\Query\ArticleCategoryList\{
    GetArticleCategoryListQuery,
    GetArticleCategoryListQueryResult
};
use App\Application\Service\TwigRenderService;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ArticleCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $roles = $this->userService->getUser()->getRoles();
        $query = GetArticleCategoryListQuery::create($request, $roles);

        /** @var GetArticleCategoryListQueryResult $result */
        $result = $this->queryBus->handle($query);

        return $this->twigRenderService->renderToResponse('domain/article_category/list.html.twig', [
            'articleCategories' => $result->articleCategories
        ]);
    }
}
