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

    public function list(Request $request, string $type): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(UserRoleConstant::SUPERVISOR->value);

        return $this->userListHandle->__invoke($request, $type);
    }
}
