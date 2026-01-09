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

use App\Application\Helper\SortFunctionHelper;
use PHPUnit\Framework\TestCase;

class SortFunctionHelperTest extends TestCase
{
    public function testUsortCzechArray(): void
    {
        $array = ['abc', 'čba', 'xyz', 'řst', 'fgh'];
        $expectedResult = ['abc', 'čba', 'fgh', 'řst', 'xyz'];

        SortFunctionHelper::usortCzechArray($array);

        $this->assertEquals($expectedResult, $array);
    }

    public function testSortCzechChars(): void
    {
        $this->assertSame(0, SortFunctionHelper::sortCzechChars('Ch', 'ch'));
        $this->assertSame(0, SortFunctionHelper::sortCzechChars('HZZ', 'hzz'));
        $this->assertGreaterThan(0, SortFunctionHelper::sortCzechChars('čba', 'abc'));
        $this->assertSame(1, SortFunctionHelper::sortCzechChars('žzz', 'zzz'));
    }
}
