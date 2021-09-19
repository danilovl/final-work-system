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

namespace App\Controller;

use App\Entity\Group;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserGroupController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.user_group.create')->handle($request);
    }

    public function edit(Request $request, Group $group): Response
    {
        return $this->get('app.http_handle.user_group.edit')->handle($request, $group);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.user_group.list')->handle($request);
    }
}
