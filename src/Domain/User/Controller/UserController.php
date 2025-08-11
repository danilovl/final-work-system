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

namespace App\Domain\User\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\Http\{
    UserEditHandle,
    UserListHandle,
    UserCreateHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class UserController
{
    public function __construct(
        private UserCreateHandle $userCreateHandle,
        private UserEditHandle $userEditHandle,
        private UserListHandle $userListHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->userCreateHandle->__invoke($request);
    }

    public function edit(Request $request, User $user): Response
    {
        return $this->userEditHandle->__invoke($request, $user);
    }

    public function list(Request $request): Response
    {
        return $this->userListHandle->__invoke($request);
    }
}
