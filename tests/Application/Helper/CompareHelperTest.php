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

namespace App\Tests\Application\Helper;

use App\Application\Constant\CompareConstant;
use App\Application\Helper\CompareHelper;
use Generator;
use PHPUnit\Framework\TestCase;

class CompareHelperTest extends TestCase
{
    /**
     * @dataProvider compareProvider
     */
    public function testCompare(
        mixed $value1,
        mixed $value2,
        CompareConstant $operator,
        bool $expectedValue
    ): void {
        $result = CompareHelper::compare($value1, $value2, $operator);

        $this->assertEquals($expectedValue, $result);
    }

    public function compareProvider(): Generator
    {
        yield [1, 0, CompareConstant::MORE, true];
        yield [0, 1, CompareConstant::MORE, false];
        yield [0, 1, CompareConstant::LESS, true];
        yield [1, 0, CompareConstant::LESS, false];
        yield [1, 1, CompareConstant::EQUAL, true];
        yield ['127.0.0.1', '127.0.0.1', CompareConstant::EQUAL, true];
        yield [1, 1, CompareConstant::NOT_EQUAL, false];
        yield [1, 0, CompareConstant::NOT_EQUAL, true];
        yield ['127.0.0.1', '127.0.0.1/24', CompareConstant::NOT_EQUAL, true];
        yield [1, 1, CompareConstant::MORE_EQUAL, true];
        yield [2, 1, CompareConstant::MORE_EQUAL, true];
        yield [1, 2, CompareConstant::MORE_EQUAL, false];
        yield [0, 1, CompareConstant::LESS_EQUAL, true];
        yield [1, 1, CompareConstant::LESS_EQUAL, true];
        yield [1, 0, CompareConstant::LESS_EQUAL, false];
    }
}
