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

namespace App\Domain\ResetPassword\Controller;

use App\Domain\ResetPassword\Http\{
    ResetPasswordResetHandle,
    ResetPasswordRequestHandle,
    ResetPasswordCheckEmailHandle
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class ResetPasswordController
{
    public function __construct(
        private ResetPasswordRequestHandle $passwordRequestHandle,
        private ResetPasswordCheckEmailHandle $resetPasswordCheckEmailHandle,
        private ResetPasswordResetHandle $resetPasswordResetHandle
    ) {}

    public function request(Request $request): Response
    {
        return $this->passwordRequestHandle->__invoke($request);
    }

    public function checkEmail(): Response
    {
        return $this->resetPasswordCheckEmailHandle->__invoke();
    }

    public function reset(Request $request): Response
    {
        return $this->resetPasswordResetHandle->__invoke($request);
    }
}
