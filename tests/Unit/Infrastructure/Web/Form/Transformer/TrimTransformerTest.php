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

namespace App\Tests\Unit\Infrastructure\Web\Form\Transformer;

use App\Infrastructure\Web\Form\Transformer\TrimTransformer;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TrimTransformerTest extends TestCase
{
    private TrimTransformer $commaDotTransformer;

    protected function setUp(): void
    {
        $this->commaDotTransformer = new TrimTransformer;
    }

    #[DataProvider('provideTransformCases')]
    public function testTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->transform($replace));
    }

    #[DataProvider('provideReverseTransformCases')]
    public function testReverseTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->reverseTransform($replace));
    }

    public static function provideTransformCases(): Generator
    {
        yield [' text', 'text'];
        yield ['text ', 'text'];
        yield [' text   ', 'text'];
        yield [' ', ''];
    }

    public static function provideReverseTransformCases(): Generator
    {
        yield ['text ', 'text '];
    }
}
