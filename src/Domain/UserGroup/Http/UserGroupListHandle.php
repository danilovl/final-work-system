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

namespace App\Domain\UserGroup\Http;

use App\Application\Service\{
    SeoPageService,
    PaginatorService,
    TwigRenderService
};
use App\Domain\UserGroup\Facade\UserGroupFacade;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupListHandle
{
    public function __construct(
        private readonly TwigRenderService $twigRenderService,
        private readonly SeoPageService $seoPageService,
        private readonly PaginatorService $paginatorService,
        private readonly UserGroupFacade $userGroupFacade
    ) {}

    public function handle(Request $request): Response
    {
        $this->seoPageService->setTitle('app.page.user_group_list');

        return $this->twigRenderService->render('user_group/list.html.twig', [
            'groups' => $this->paginatorService->createPaginationRequest(
                $request,
                $this->userGroupFacade->queryAll()
            ),
        ]);
    }
}
