<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\User\Controller;

use App\Controller\BaseController;
use App\Entity\User;
use App\Model\User\Http\{
    UserListHandle,
    UserEditHandle,
    UserCreateHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserController extends BaseController
{
    public function __construct(
        private UserCreateHandle $userCreateHandle,
        private UserEditHandle $userEditHandle,
        private UserListHandle $userListHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->userCreateHandle->handle($request);
    }

    public function edit(Request $request, User $user): Response
    {
        return $this->userEditHandle->handle($request, $user);
    }

    public function list(Request $request): Response
    {
        return $this->userListHandle->handle($request);
    }
}
