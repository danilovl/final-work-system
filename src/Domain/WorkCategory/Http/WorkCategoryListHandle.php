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

namespace App\Domain\WorkCategory\Http;

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\WorkCategory\Bus\Query\WorkCategoryList\{
    GetWorkCategoryListQuery,
    GetWorkCategoryListQueryResult
};
use App\Application\Service\TwigRenderService;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $query = GetWorkCategoryListQuery::create($request, $user);
        /** @var GetWorkCategoryListQueryResult $result */
        $result = $this->queryBus->handle($query);

        return $this->twigRenderService->renderToResponse('domain/work_category/list.html.twig', [
            'workCategories' => $result->workCategories
        ]);
    }
}
