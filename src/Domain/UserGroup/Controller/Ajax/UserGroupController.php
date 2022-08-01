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

namespace App\Domain\UserGroup\Controller\Ajax;

use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Http\Ajax\{
    UserGroupEditHandle,
    UserGroupCreateHandle,
    UserGroupDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserGroupController extends AbstractController
{
    public function __construct(
        private readonly UserGroupCreateHandle $userGroupCreateHandle,
        private readonly UserGroupEditHandle $userGroupEditHandle,
        private readonly UserGroupDeleteHandle $userGroupDeleteHandle
    ) {}

    public function create(Request $request): JsonResponse
    {
        return $this->userGroupCreateHandle->handle($request);
    }

    public function edit(Request $request, Group $group): JsonResponse
    {
        return $this->userGroupEditHandle->handle($request, $group);
    }

    public function delete(Group $group): JsonResponse
    {
        return $this->userGroupDeleteHandle->handle($group);
    }
}
