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

namespace App\Domain\User\Http\Api;

use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Entity\User;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use App\Domain\User\Bus\Query\UserList\{
    GetUserListQuery,
    GetUserListQueryResult
};
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use App\Domain\User\DTO\Api\Output\{
    UserListOutput,
    UserListItemDTO
};
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Api\WorkDTO;
use App\Domain\Work\Bus\Query\WorksByUsersSupervisorStatus\{GetWorksByUsersSupervisorStatusQuery, GetWorksByUsersSupervisorStatusQueryResult};

readonly class UserListHandle
{
    public function __construct(
        private UserService $userService,
        private QueryBusInterface $queryBus,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $user = $this->userService->getUser();

        $query = GetUserListQuery::create(
            request: $request,
            user: $user,
            type: $type
        );

        /** @var GetUserListQueryResult $result */
        $result = $this->queryBus->handle($query);
        $pagination = $result->users;

        $userListItems = [];
        $worksByUser = [];

        if ($this->shouldGetUserWorkAndStatus($type)) {
            $userIds = [];
            /** @var User $paginationUser */
            foreach ($pagination as $paginationUser) {
                $userIds[] = $paginationUser->getId();
            }

            $getWorksByUsersSupervisorStatusQuery = GetWorksByUsersSupervisorStatusQuery::create(
                userIds: $userIds,
                supervisor: $user,
                type: $type,
                workStatus: null
            );
            /** @var GetWorksByUsersSupervisorStatusQueryResult $getWorksByUsersSupervisorStatusQueryResult */
            $getWorksByUsersSupervisorStatusQueryResult = $this->queryBus->handle($getWorksByUsersSupervisorStatusQuery);
            $worksByUser = $getWorksByUsersSupervisorStatusQueryResult->works;
        }

        /** @var User $paginationUser */
        foreach ($pagination as $paginationUser) {
            $userDTO = $this->objectToDtoMapper->map($paginationUser, UserDTO::class);
            $userWorks = [];

            foreach ($worksByUser[$paginationUser->getId()] ?? [] as $work) {
                $userWorks[] = $this->objectToDtoMapper->map($work, WorkDTO::class);
            }

            $userListItems[] = new UserListItemDTO(
                user: $userDTO,
                works: $userWorks
            );
        }

        $result = new UserListOutput(
            $pagination->getItemNumberPerPage(),
            $pagination->getTotalItemCount(),
            $pagination->count(),
            $userListItems
        );

        return new JsonResponse($result);
    }

    private function shouldGetUserWorkAndStatus(string $type): bool
    {
        return match ($type) {
            WorkUserTypeConstant::AUTHOR->value,
            WorkUserTypeConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value => true,
            default => false,
        };
    }
}
