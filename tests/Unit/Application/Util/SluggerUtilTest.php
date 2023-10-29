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

use App\Application\Util\SluggerUtil;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SluggerUtilTest extends TestCase
{
    #[DataProvider('slugifyDataProvider')]
    public function testSlugify(string $input, string $expectedOutput)
    {
        $slugger = new SluggerUtil;
        $this->assertEquals($expectedOutput, $slugger->slugify($input));
    }

    public static function slugifyDataProvider(): Generator
    {
        yield ['Hello World', 'hello-world'];
        yield ['Lorem Ipsum Dolor Sit Amet', 'lorem-ipsum-dolor-sit-amet'];
        yield ['   Trim Spaces   ', 'trim-spaces'];
        yield ['<h1>HTML Tags</h1>', 'html-tags'];
    }
}
