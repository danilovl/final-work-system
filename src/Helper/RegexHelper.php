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

class RegexHelper
{
    public static function allLinks(string $text): ?array
    {
        if (preg_match_all('~<a.*?href="([^"]+)"(.*?)>~', $text, $matches, PREG_SET_ORDER) > 0) {
            return $matches;
        }

        return null;
    }
}
