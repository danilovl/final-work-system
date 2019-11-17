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

namespace FinalWork\FinalWorkBundle\Tests;

use Exception;
use FinalWork\FinalWorkBundle\Helper\FunctionHelper;
use Generator;
use PHPUnit\Framework\TestCase;

class FunctionHelperTest extends TestCase
{
    /**
     * @dataProvider randomPasswordProvider
     * @param int $length
     * @throws Exception
     */
    public function testRandomPassword(int $length): void
    {
        $passwordLength = strlen(FunctionHelper::randomPassword($length));

        $this->assertEquals($length, $passwordLength);
    }

    /**
     * @return Generator
     */
    public function randomPasswordProvider(): Generator
    {
        yield [1];
        yield [2];
        yield [20];
        yield [100];
    }

    /**
     * @dataProvider compareSimpleTwoArrayProvider
     * @param array $one
     * @param array $two
     * @param bool $isValid
     */
    public function testCompareSimpleTwoArray(array $one, array $two, bool $isValid): void
    {
        $compare = FunctionHelper::compareSimpleTwoArray($one, $two);

        $this->assertEquals($compare, $isValid);
    }

    /**
     * @return Generator
     */
    public function compareSimpleTwoArrayProvider(): Generator
    {
        yield [[1, 2, 3], [2, 1, 3], true];
        yield [['a', 'b', 'c'], ['b', 'a', 'c'], true];
        yield [['a', 'b', 'c'], ['a', 'b', 'b', 'c'], false];
        yield [[1, 2, 3], ['a', 'b', 'c'], false];
    }

    /**
     * @dataProvider checkIntersectTwoArrayProvider
     * @param array $one
     * @param array $two
     * @param bool $isValid
     */
    public function testCheckIntersectTwoArray(array $one, array $two, bool $isValid): void
    {
        $check = FunctionHelper::checkIntersectTwoArray($one, $two);

        $this->assertEquals($check, $isValid);
    }

    /**
     * @return Generator
     */
    public function checkIntersectTwoArrayProvider(): Generator
    {
        yield [[2, 2, 2], [2, 2, 3], true];
        yield [[2, 2, 2], [2, 2, 2], true];
        yield [[2, 2, 2], [3, 3, 3], false];
    }

    /**
     * @dataProvider sanitizeFileNameProvider
     * @param string $dangerousFilename
     * @param string $platform
     * @param string $result
     */
    public function testSanitizeFileName(
        string $dangerousFilename,
        string $platform,
        string $result
    ): void {
        $sanitizeFileName = FunctionHelper::sanitizeFileName($dangerousFilename, $platform);

        $this->assertEquals($sanitizeFileName, $result);
    }

    /**
     * @return Generator
     */
    public function sanitizeFileNameProvider(): Generator
    {
        yield ['test?test?file.pdf', 'unix', 'test_test_file.pdf'];
        yield ['test#test?test/file.pdf', 'linux', 'test_test_test_file.pdf'];
        yield ['#test?test/test.pdf', 'windows', '#test?test/test.pdf'];
    }
}
