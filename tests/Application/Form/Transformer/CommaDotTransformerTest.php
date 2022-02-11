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

use App\Application\Form\Transformer\CommaDotTransformer;
use Generator;
use PHPUnit\Framework\TestCase;

class CommaDotTransformerTest extends TestCase
{
    private CommaDotTransformer $commaDotTransformer;

    public function setUp(): void
    {
        $this->commaDotTransformer = new CommaDotTransformer;
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
        yield ['11111,11111', '11111.11111'];
        yield ['50,0527973', '50.0527973'];
        yield ['50,05,27,97,3', '50.05.27.97.3'];
    }

    public function reverseTransformProvider(): Generator
    {
        yield ['50.0527973', '50.0527973'];
        yield ['1', '1'];
    }
}
