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

use App\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class UserController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.user.create')->handle($request);
    }

    public function edit(Request $request, User $user): Response
    {
        return $this->get('app.http_handle.user.edit')->handle($request, $user);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.user.list')->handle($request);
    }
}
