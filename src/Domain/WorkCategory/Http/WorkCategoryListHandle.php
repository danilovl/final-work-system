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

use App\Application\Service\{
    PaginatorService,
    TwigRenderService
};
use App\Domain\User\Service\UserService;
use App\Domain\WorkCategory\Facade\WorkCategoryFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class WorkCategoryListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private PaginatorService $paginatorService,
        private WorkCategoryFacade $workCategoryFacade
    ) {}

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();

        return $this->twigRenderService->render('work_category/list.html.twig', [
            'workCategories' => $this->paginatorService->createPaginationRequest(
                $request,
                $this->workCategoryFacade->queryWorkCategoriesByOwner($user)
            )
        ]);
    }
}
