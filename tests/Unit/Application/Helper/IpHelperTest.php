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

namespace App\Tests\Unit\Application\Helper;

use App\Application\Helper\IpHelper;
use Generator;
use PHPUnit\Framework\TestCase;

class IpHelperTest extends TestCase
{
    /**
     * @dataProvider ipInRangeProvider
     */
    public function testIsIpInRange(string $ip, string $range, bool $result): void
    {
        $isIpInRange = IpHelper::isIpInRange($ip, $range);
        $this->assertEquals($isIpInRange, $result);
    }

    /**
     * @dataProvider ipAllowedProvider
     */
    public function testisIpAllowed(string $ip, array $whiteList, bool $result): void
    {
        $isIpAllowed = IpHelper::isIpAllowed($ip, $whiteList);
        $this->assertEquals($isIpAllowed, $result);
    }

    public function ipInRangeProvider(): Generator
    {
        yield ['127.0.0.1', '127.0.0.1/24', true];
        yield ['192.0.0.60', '192.0.0.60/24', true];
    }

    public function ipAllowedProvider(): Generator
    {
        yield ['127.0.0.1', ['127.0.0.1/24'], true];
        yield ['192.0.0.60', ['127.0.0.1/24'], false];
    }
}
