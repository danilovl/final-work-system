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

namespace App\Exception;

use Exception;

final class ExpiredResetPasswordTokenException extends Exception implements ResetPasswordExceptionInterface
{
    public function getReason(): string
    {
        return 'app.exception.reset_password_expired_token';
    }
}
