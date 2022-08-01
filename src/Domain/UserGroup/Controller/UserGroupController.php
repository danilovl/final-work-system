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

namespace App\Domain\UserGroup\Controller;

use App\Domain\UserGroup\Http\{
    UserGroupEditHandle,
    UserGroupListHandle,
    UserGroupCreateHandle
};
use App\Domain\UserGroup\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupController extends AbstractController
{
    public function __construct(
        private readonly UserGroupCreateHandle $userGroupCreateHandle,
        private readonly UserGroupEditHandle $userGroupEditHandle,
        private readonly UserGroupListHandle $userGroupListHandle
    ) {}

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
