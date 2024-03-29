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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileShowHandle $profileShowHandle,
        private readonly ProfileEditHandle $profileEditHandle,
        private readonly ProfileChangeImageHandle $profileChangeImageHandle,
        private readonly ProfileDeleteImageHandle $profileDeleteImageHandle,
        private readonly ProfileChangePasswordHandle $profileChangePasswordHandle,
        private readonly ProfileImageHandle $profileImageHandle
    ) {}

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

    public function image(User $user): BinaryFileResponse|Response
    {
        return $this->profileImageHandle->handle($user);
    }
}
