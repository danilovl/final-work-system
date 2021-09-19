<?php declare(strict_types=1);

/**
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
    Response
};

class ResetPasswordController extends BaseController
{
    public function request(Request $request): Response
    {
        return $this->get('app.http_handle.reset_password.request')->handle($request);
    }

    public function checkEmail(): Response
    {
        return $this->get('app.http_handle.reset_password.check_email')->handle();
    }

    public function reset(Request $request): Response
    {
        return $this->get('app.http_handle.reset_password.reset')->handle($request);
    }
}
