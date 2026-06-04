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
use App\Domain\User\Http\Api\{
    UserListHandle,
    UserDetailHandle
};
use App\Infrastructure\Service\AuthorizationCheckerService;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class UserController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private UserDetailHandle $userDetailHandle,
        private UserListHandle $userListHandle
    ) {}

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
