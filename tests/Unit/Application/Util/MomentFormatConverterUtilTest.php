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

namespace App\Tests\Unit\Application\Util;

use App\Application\Util\MomentFormatConverterUtil;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MomentFormatConverterUtilTest extends TestCase
{
    #[DataProvider('convertDataProvider')]
    public function testConvert(string $format, string $expectedOutput): void
    {
        $slugger = new MomentFormatConverterUtil;
        $this->assertEquals($expectedOutput, $slugger->convert($format));
    }

    public static function convertDataProvider(): Generator
    {
        yield ['yyyy-MM-dd', 'YYYY-MM-DD'];
        yield ['dd.MM.yy', 'DD.MM.YY'];
        yield ['yyyy', 'YYYY'];
        yield ['EEEE', 'dddddd'];
        yield ['ZZZ', 'ZZ'];
        yield ['d MMM y', 'D MMM YYYY'];
        yield ['hh:mm:ss a', 'hh:mm:ss a'];
    }
}
