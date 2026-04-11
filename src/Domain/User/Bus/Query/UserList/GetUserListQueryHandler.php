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

namespace App\Domain\User\Bus\Query\UserList;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Infrastructure\Service\PaginatorService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\Work\Constant\WorkUserTypeConstant;

readonly class GetUserListQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private PaginatorService $paginatorService,
        private UserFacade $userFacade
    ) {}

    public function __invoke(GetUserListQuery $query): GetUserListQueryResult
    {
        $usersQuery = match ($query->type) {
            WorkUserTypeConstant::AUTHOR->value,
            WorkUserTypeConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value => $this->userFacade->queryBySupervisor(
                $query->user,
                $query->type,
                $query->workStatus
            ),
            default => $this->userFacade->queryUnusedUsers($query->user)
        };

        $usersQuery->setHydrationMode(User::class);
        $pagination = $this->paginatorService->createPaginationRequest($query->request, $usersQuery);

        return new GetUserListQueryResult(users: $pagination);
    }
}
