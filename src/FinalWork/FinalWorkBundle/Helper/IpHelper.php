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

namespace FinalWork\FinalWorkBundle\Helper;

class IpHelper
{
	/**
	 * @param string $ip
	 * @param string $range
	 * @return bool
	 */
	public static function isIpInRange(string $ip, string $range): bool
	{
		if (strpos($range, '/') === false) {
			$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		[$range, $netmask] = explode('/', $range, 2);

		$range_decimal = ip2long($range);
		$ip_decimal = ip2long($ip);
		$wildcard_decimal = (2 ** (32 - $netmask)) - 1;
		$netmask_decimal = ~$wildcard_decimal;

		return ($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal);
	}

	/**
	 * @param string $ip
	 * @param array $whiteList
	 * @return bool
	 */
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
