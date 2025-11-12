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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\UserGroup\Bus\Query\UserGroupList\{
    GetUserGroupListQuery,
    GetUserGroupListQueryResult
};
use App\Application\Service\{
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserGroupListHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $query = GetUserGroupListQuery::create($request);
        /** @var GetUserGroupListQueryResult $result */
        $result = $this->queryBus->handle($query);

        $this->seoPageService->setTitle('app.page.user_group_list');

        return $this->twigRenderService->renderToResponse('domain/user_group/list.html.twig', [
            'groups' => $result->groups
        ]);
    }
}
