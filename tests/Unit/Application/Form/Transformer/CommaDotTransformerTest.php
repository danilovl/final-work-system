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

namespace App\Tests\Unit\Application\Form\Transformer;

use App\Application\Exception\RuntimeException;
use App\Application\Form\Transformer\CommaDotTransformer;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class CommaDotTransformerTest extends TestCase
{
    private CommaDotTransformer $commaDotTransformer;

    public function setUp(): void
    {
        $this->commaDotTransformer = new CommaDotTransformer;
    }

    #[DataProvider('transformProvider')]
    public function testTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->transform($replace));
    }

    #[DataProvider('reverseTransformProvider')]
    public function testReverseTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->reverseTransform($replace));
    }

    public function testRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->commaDotTransformer->reverseTransform(new stdClass);

        $this->expectException(RuntimeException::class);
        $this->commaDotTransformer->reverseTransform('not numeric');
    }

    public static function transformProvider(): Generator
    {
        yield ['11111,11111', '11111.11111'];
        yield ['50,0527973', '50.0527973'];
        yield ['50,05,27,97,3', '50.05.27.97.3'];
    }

    public static function reverseTransformProvider(): Generator
    {
        yield ['50.0527973', '50.0527973'];
        yield ['1', '1'];
    }
}
