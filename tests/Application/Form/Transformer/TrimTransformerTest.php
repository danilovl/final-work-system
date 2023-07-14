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

namespace App\Tests\Application\Form\Transformer;

use App\Application\Form\Transformer\TrimTransformer;
use Generator;
use PHPUnit\Framework\TestCase;

class TrimTransformerTest extends TestCase
{
    private TrimTransformer $commaDotTransformer;

    public function setUp(): void
    {
        $this->commaDotTransformer = new TrimTransformer;
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->transform($replace));
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform(string $replace, string $expected): void
    {
        $this->assertEquals($expected, $this->commaDotTransformer->reverseTransform($replace));
    }

    public function transformProvider(): Generator
    {
        yield [' text', 'text'];
        yield ['text ', 'text'];
        yield [' text   ', 'text'];
        yield [' ', ''];
    }

    public function reverseTransformProvider(): Generator
    {
        yield ['text ', 'text '];
    }
}