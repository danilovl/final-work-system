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

use App\Application\Exception\RuntimeException;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\DTO\Api\Output\UserSearchOutput;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ObjectDtoMapper\Service\ObjectToDtoMapperInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class UserSearchHandle
{
    public function __construct(
        private UserFacade $userFacade,
        private PaginatorService $paginatorService,
        private ObjectToDtoMapperInterface $objectToDtoMapper
    ) {}

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $role = match ($type) {
            WorkUserTypeConstant::AUTHOR->value => UserRoleConstant::STUDENT->value,
            WorkUserTypeConstant::OPPONENT->value => UserRoleConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value => UserRoleConstant::CONSULTANT->value,
            default => throw new RuntimeException("Unsupported user type '{$type}' for search."),
        };

        $search = $request->query->get('search') ?? $request->query->get('query') ?? $request->query->get('term');
        if (is_string($search) && mb_trim($search) === '') {
            $search = null;
        }

        $query = $this->userFacade->queryAllByUserRoleAndSearch(
            role: $role,
            search: is_string($search) ? $search : null,
            enable: true
        );

        $pagination = $this->paginatorService->createPaginationRequest($request, $query);

        $users = [];
        /** @var User $user */
        foreach ($pagination as $user) {
            $users[] = $this->objectToDtoMapper->map($user, UserDTO::class);
        }

        $output = new UserSearchOutput(
            numItemsPerPage: $pagination->getItemNumberPerPage(),
            totalCount: $pagination->getTotalItemCount(),
            currentItemCount: $pagination->count(),
            result: $users
        );

        return new JsonResponse($output);
    }
}
