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

namespace App\Model\ResetPassword\Factory;

use App\Model\BaseModelFactory;
use App\Model\ResetPassword\Entity\ResetPassword;
use App\Model\ResetPassword\ResetPasswordModel;

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
