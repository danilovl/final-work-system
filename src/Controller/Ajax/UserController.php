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
use App\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class UserController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        return $this->get('app.http_handle_ajax.user.create')->handle($request);
    }

    public function edit(Request $request, User $user): JsonResponse
    {
        return $this->get('app.http_handle_ajax.user.edit')->handle($request, $user);
    }
}
