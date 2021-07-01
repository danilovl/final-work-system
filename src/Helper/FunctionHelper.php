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

namespace App\Helper;

use App\Constant\PlatformConstant;

class FunctionHelper
{
    public static function compareSimpleTwoArray(array $one, array $two): bool
    {
        sort($one);
        sort($two);

        return $one === $two;
    }

    public static function checkIntersectTwoArray(array $one, array $two): bool
    {
        $intersect = array_intersect($one, $two);

        return count($intersect) > 0;
    }

    public static function randomPassword(int $length): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $n = random_int(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass);
    }

    public static function sanitizeFileName(
        string $dangerousFilename,
        string $platform = PlatformConstant::UNIX
    ): string {
        if (in_array(strtolower($platform), [PlatformConstant::UNIX, PlatformConstant::LINUX])) {
            $dangerousCharacters = [' ', '"', "'", '&', '/', "\\", '?', '#'];
        } else {
            return $dangerousFilename;
        }

        return str_replace($dangerousCharacters, '_', $dangerousFilename);
    }
}
