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

namespace App\Helper;

class HashHelper
{
    public static function generateDefaultHash(): ?string
    {
        return sha1(uniqid((string) mt_rand(), true));
    }

    public static function generateResetPasswordHashedToken(string $data, string $signingKey): string
    {
        return base64_encode(hash_hmac('sha256', $data, $signingKey, true));
    }

    public static function generateUserSalt(): string
    {
        return rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
    }
}
