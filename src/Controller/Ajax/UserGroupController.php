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

namespace App\Controller\Ajax;

use App\Controller\BaseController;
use App\Entity\Group;
use App\Model\UserGroup\Http\Ajax\{
    UserGroupEditHandle,
    UserGroupCreateHandle,
    UserGroupDeleteHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserGroupController extends BaseController
{
    public function __construct(
        private UserGroupCreateHandle $userGroupCreateHandle,
        private UserGroupEditHandle $userGroupEditHandle,
        private UserGroupDeleteHandle $userGroupDeleteHandle
    ) {
    }

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
