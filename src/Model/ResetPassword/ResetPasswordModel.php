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

namespace App\Model\ResetPassword;

use App\Model\User\Entity\User;
use DateTime;

class ResetPasswordModel
{
    public ?User $user;
    public ?string $hashedToken;
    public ?DateTime $expiresAt;
}
