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

namespace App\Model\UserGroup\Controller;

use App\Controller\BaseController;
use App\Entity\Group;
use App\Model\UserGroup\Http\{
    UserGroupListHandle,
    UserGroupEditHandle,
    UserGroupCreateHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupController extends BaseController
{
    public function __construct(
        private UserGroupCreateHandle $userGroupCreateHandle,
        private UserGroupEditHandle $userGroupEditHandle,
        private UserGroupListHandle $userGroupListHandle
    ) {
    }

    public function create(Request $request): Response
    {
        return $this->userGroupCreateHandle->handle($request);
    }

    public function edit(Request $request, Group $group): Response
    {
        return $this->userGroupEditHandle->handle($request, $group);
    }

    public function list(Request $request): Response
    {
        return $this->userGroupListHandle->handle($request);
    }
}
