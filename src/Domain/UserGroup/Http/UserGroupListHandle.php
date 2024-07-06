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

readonly class UserGroupListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private PaginatorService $paginatorService,
        private UserGroupFacade $userGroupFacade
    ) {}

    public function handle(Request $request): Response
    {
        $this->seoPageService->setTitle('app.page.user_group_list');

        $pagination = $this->paginatorService->createPaginationRequest(
            $request,
            $this->userGroupFacade->queryAll(),
            detachEntity: true
        );

        return $this->twigRenderService->renderToResponse('domain/user_group/list.html.twig', [
            'groups' => $pagination
        ]);
    }
}
