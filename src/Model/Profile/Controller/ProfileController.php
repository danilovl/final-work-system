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

namespace App\Model\Profile\Controller;

use App\Controller\BaseController;
use App\Model\Profile\Http\{
    ProfileEditHandle,
    ProfileShowHandle,
    ProfileChangeImageHandle,
    ProfileDeleteImageHandle,
    ProfileChangePasswordHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};

class ProfileController extends BaseController
{
    public function __construct(
        private ProfileShowHandle $profileShowHandle,
        private ProfileEditHandle $profileEditHandle,
        private ProfileChangeImageHandle $profileChangeImageHandle,
        private ProfileDeleteImageHandle $profileDeleteImageHandle,
        private ProfileChangePasswordHandle $profileChangePasswordHandle
    ) {
    }

    public function show(): Response
    {
        return $this->profileShowHandle->handle();
    }

    public function edit(Request $request): Response
    {
        return $this->profileEditHandle->handle($request);
    }

    public function changeImage(Request $request): Response
    {
        return $this->profileChangeImageHandle->handle($request);
    }

    public function deleteImage(): RedirectResponse
    {
        return $this->profileDeleteImageHandle->handle();
    }

    public function changePassword(Request $request): Response
    {
        return $this->profileChangePasswordHandle->handle($request);
    }
}
