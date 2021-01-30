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

class IpHelper
{
    public static function isIpInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            $range .= '/32';
        }

        [$range, $netmask] = explode('/', $range, 2);

        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = (2 ** (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;

        return ($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal);
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
