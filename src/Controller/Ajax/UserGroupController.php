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
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserGroupController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.user_group.create')->handle($request);
    }

    public function edit(Request $request, Group $group): JsonResponse
    {
        return $this->get('app.http_handle_ajax.user_group.edit')->handle($request, $group);
    }

    public function delete(Group $group): JsonResponse
    {
        return $this->get('app.http_handle_ajax.user_group.delete')->handle($group);
    }
}
