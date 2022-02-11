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

namespace App\Domain\ResetPassword\Factory;

use App\Domain\BaseModelFactory;
use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\ResetPasswordModel;

class ResetPasswordFactory extends BaseModelFactory
{
    public function flushFromModel(
        ResetPasswordModel $resetPasswordModel,
        ResetPassword $resetPassword = null
    ): ResetPassword {
        $resetPassword = $resetPassword ?? new ResetPassword;
        $resetPassword = $this->fromModel($resetPassword, $resetPasswordModel);

        $this->entityManagerService->persistAndFlush($resetPassword);

        return $resetPassword;
    }

    public function fromModel(
        ResetPassword $resetPassword,
        ResetPasswordModel $resetPasswordModel
    ): ResetPassword {
        $resetPassword->setUser($resetPasswordModel->user);
        $resetPassword->setHashedToken($resetPasswordModel->hashedToken);
        $resetPassword->setExpiresAt($resetPasswordModel->expiresAt);

        return $resetPassword;
    }
}
