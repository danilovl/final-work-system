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

namespace App\Application\Helper;

class IpHelper
{
    public static function isIpInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            $range .= '/32';
        }

        [$range, $netmask] = explode('/', $range, 2);

        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = (2 ** (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return ($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal);
    }

    public static function isIpAllowed(string $ip, array $whiteList = []): bool
    {
        foreach ($whiteList as $ipRange) {
            if (self::isIpInRange($ip, $ipRange)) {
                return true;
            }
        }

        return false;
    }
}
