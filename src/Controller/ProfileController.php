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

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class ProfileController extends BaseController
{
    public function show(): Response
    {
        return $this->get('app.http_handle.profile.show')->handle();
    }

    public function edit(Request $request): Response
    {
        return $this->get('app.http_handle.profile.edit')->handle($request);
    }

    public function changeImage(Request $request): Response
    {
        return $this->get('app.http_handle.profile.change_image')->handle($request);
    }

    public function deleteImage(): RedirectResponse
    {
        return $this->get('app.http_handle.profile.delete_image')->handle();
    }

    public function changePassword(Request $request): Response
    {
        return $this->get('app.http_handle.profile.change_image')->handle($request);
    }
}
