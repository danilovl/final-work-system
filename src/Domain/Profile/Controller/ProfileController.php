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

namespace App\Domain\Profile\Controller;

use App\Domain\User\Entity\User;
use App\Domain\Profile\Http\{
    ProfileEditHandle,
    ProfileShowHandle,
    ProfileImageHandle,
    ProfileChangeImageHandle,
    ProfileDeleteImageHandle,
    ProfileChangePasswordHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse,
    BinaryFileResponse
};

readonly class ProfileController
{
    public function __construct(
        private ProfileShowHandle $profileShowHandle,
        private ProfileEditHandle $profileEditHandle,
        private ProfileChangeImageHandle $profileChangeImageHandle,
        private ProfileDeleteImageHandle $profileDeleteImageHandle,
        private ProfileChangePasswordHandle $profileChangePasswordHandle,
        private ProfileImageHandle $profileImageHandle
    ) {}

    public function show(): Response
    {
        return $this->profileShowHandle->__invoke();
    }

    public function edit(Request $request): Response
    {
        return $this->profileEditHandle->__invoke($request);
    }

    public function changeImage(Request $request): Response
    {
        return $this->profileChangeImageHandle->__invoke($request);
    }

    public function deleteImage(): RedirectResponse
    {
        return $this->profileDeleteImageHandle->__invoke();
    }

    public function changePassword(Request $request): Response
    {
        return $this->profileChangePasswordHandle->__invoke($request);
    }

    public function image(User $user): BinaryFileResponse|Response
    {
        return $this->profileImageHandle->__invoke($user);
    }
}
