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

namespace App\Domain\User\Controller\Ajax;

use App\Domain\User\Entity\User;
use App\Domain\User\Http\Ajax\{
    UserEditHandle,
    UserCreateHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    public function __construct(
        private UserCreateHandle $userCreateHandle,
        private UserEditHandle $userEditHandle
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->userCreateHandle->handle($request);
    }

    public function edit(Request $request, User $user): JsonResponse
    {
        return $this->userEditHandle->handle($request, $user);
    }
}
