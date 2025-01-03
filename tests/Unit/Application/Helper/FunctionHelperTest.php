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

use App\Application\Constant\PlatformConstant;
use App\Application\Helper\FunctionHelper;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FunctionHelperTest extends TestCase
{
    #[DataProvider('randomPasswordProvider')]
    public function testRandomPassword(int $length): void
    {
        $passwordLength = mb_strlen(FunctionHelper::randomPassword($length));

        $this->assertEquals($length, $passwordLength);
    }

    public static function randomPasswordProvider(): Generator
    {
        yield [1];
        yield [2];
        yield [20];
        yield [100];
    }

    #[DataProvider('compareSimpleTwoArrayProvider')]
    public function testCompareSimpleTwoArray(array $one, array $two, bool $isValid): void
    {
        $compare = FunctionHelper::compareSimpleTwoArray($one, $two);

        $this->assertEquals($compare, $isValid);
    }

    public static function compareSimpleTwoArrayProvider(): Generator
    {
        yield [[1, 2, 3], [2, 1, 3], true];
        yield [['a', 'b', 'c'], ['b', 'a', 'c'], true];
        yield [['a', 'b', 'c'], ['a', 'b', 'b', 'c'], false];
        yield [[1, 2, 3], ['a', 'b', 'c'], false];
    }

    #[DataProvider('checkIntersectTwoArrayProvider')]
    public function testCheckIntersectTwoArray(array $one, array $two, bool $isValid): void
    {
        $check = FunctionHelper::checkIntersectTwoArray($one, $two);

        $this->assertEquals($check, $isValid);
    }

    public static function checkIntersectTwoArrayProvider(): Generator
    {
        yield [[2, 2, 2], [2, 2, 3], true];
        yield [[2, 2, 2], [2, 2, 2], true];
        yield [[2, 2, 2], [3, 3, 3], false];
    }

    #[DataProvider('sanitizeFileNameProvider')]
    public function testSanitizeFileName(
        string $dangerousFilename,
        PlatformConstant $platform,
        string $result
    ): void {
        $sanitizeFileName = FunctionHelper::sanitizeFileName($dangerousFilename, $platform);

        $this->assertEquals($sanitizeFileName, $result);
    }

    public static function sanitizeFileNameProvider(): Generator
    {
        yield ['test?test?file.pdf', PlatformConstant::UNIX, 'test_test_file.pdf'];
        yield ['test#test?test/file.pdf', PlatformConstant::LINUX, 'test_test_test_file.pdf'];
        yield ['#test?test/test.pdf', PlatformConstant::WINDOWS, '#test?test/test.pdf'];
    }
}
