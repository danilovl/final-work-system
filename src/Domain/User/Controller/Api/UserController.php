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

namespace App\Domain\User\Controller\Api;

use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\DTO\Api\Output\UserListOutput;
use App\Domain\User\DTO\Api\UserDetailDTO;
use App\Domain\User\Http\Api\{
    UserListHandle,
    UserDetailHandle
};
use App\Infrastructure\Service\AuthorizationCheckerService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

#[OA\Tag(name: 'User')]
readonly class UserController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private UserDetailHandle $userDetailHandle,
        private UserListHandle $userListHandle
    ) {}

    #[OA\Get(
        path: '/api/key/users/detail',
        description: 'Retrieves detailed information about the current authenticated user.',
        summary: 'User detail'
    )]
    #[OA\Post(
        path: '/api/key/users/detail',
        description: 'Retrieves detailed information about the current authenticated user.',
        summary: 'User detail'
    )]
    #[OA\Response(
        response: 200,
        description: 'User detail',
        content: new OA\JsonContent(ref: new Model(type: UserDetailDTO::class))
    )]
    public function detail(): JsonResponse
    {
        return $this->userDetailHandle->__invoke();
    }

    #[OA\Get(
        path: '/api/key/users/{type}',
        description: 'Retrieves a paginated list of users filtered by role in work (author, opponent, consultant) or unused.',
        summary: 'User list'
    )]
    #[OA\Parameter(
        name: 'type',
        description: 'User relation to a work to filter by',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: ['author', 'opponent', 'consultant', 'unused'])
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number for pagination',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page for pagination',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'User list',
        content: new OA\JsonContent(ref: new Model(type: UserListOutput::class))
    )]
    public function list(Request $request, string $type): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(UserRoleConstant::SUPERVISOR->value);

        return $this->userListHandle->__invoke($request, $type);
    }
}
