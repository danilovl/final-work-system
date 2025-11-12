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

namespace App\Domain\UserGroup\Bus\Query\UserGroupList;

use App\Application\Service\PaginatorService;
use App\Domain\UserGroup\Facade\UserGroupFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetUserGroupListQueryHandler
{
    public function __construct(
        private UserGroupFacade $userGroupFacade,
        private PaginatorService $paginatorService
    ) {}

    public function __invoke(GetUserGroupListQuery $query): GetUserGroupListQueryResult
    {
        $queryAll = $this->userGroupFacade->queryAll();

        $pagination = $this->paginatorService->createPaginationRequest(
            $query->request,
            $queryAll,
            detachEntity: true
        );

        return new GetUserGroupListQueryResult($pagination);
    }
}
