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

use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Application\Mapper\ObjectToDtoMapper;
use App\Domain\User\Entity\User;
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
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Facade\WorkFacade;

readonly class UserListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkFacade $workFacade,
        private QueryBusInterface $queryBus,
        private ObjectToDtoMapper $objectToDtoMapper,
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
        $getUserWorkAndStatus = $this->shouldGetUserWorkAndStatus($type);

        /** @var User $paginationUser */
        foreach ($pagination as $paginationUser) {
            $userDTO = $this->objectToDtoMapper->map($paginationUser, UserDTO::class);
            $userWorks = [];

            if ($getUserWorkAndStatus === true) {
                $workRepositoryDTO = new WorkRepositoryDTO(
                    user: $paginationUser,
                    supervisor: $user,
                    type: $type
                );

                $paginationUserWorks = $this->workFacade->listByAuthorSupervisorStatus($workRepositoryDTO);
                
                foreach ($paginationUserWorks as $work) {
                    $userWorks[] = $this->objectToDtoMapper->map($work, WorkDTO::class);

                }
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
