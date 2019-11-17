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

namespace FinalWork\FinalWorkBundle\Helper;

use Exception;

class FunctionHelper
{
    /**
     * @param array $one
     * @param array $two
     * @return bool
     */
    public static function compareSimpleTwoArray(array $one, array $two): bool
    {
        sort($one);
        sort($two);

        return $one === $two;
    }

    /**
     * @param array $one
     * @param array $two
     * @return bool
     */
    public static function checkIntersectTwoArray(array $one, array $two): bool
    {
        $intersect = array_intersect($one, $two);

        return count($intersect) > 0;
    }

    /**
     * @param int $length
     * @return string
     *
     * @throws Exception
     */
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

    /**
     * @param string $dangerousFilename
     * @param string $platform
     * @return string
     */
    public static function sanitizeFileName(string $dangerousFilename, string $platform = 'Unix'): string
    {
        if (in_array(strtolower($platform), ['unix', 'linux'])) {
            $dangerousCharacters = [' ', '"', "'", '&', '/', "\\", '?', '#'];
        } else {
            return $dangerousFilename;
        }

        return str_replace($dangerousCharacters, '_', $dangerousFilename);
    }
}
