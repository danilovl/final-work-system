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

namespace App\Tests\Unit\Application\Transformer\Api;

use App\Application\Transformer\Api\Transformer;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    private readonly object $object;
    private readonly Transformer $transformer;

    protected function setUp(): void
    {
        $parameterService = $this->createMock(ParameterServiceInterface::class);
        $parameterService->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->transformer = new Transformer($parameterService);

        $this->object = new class {
            public function getName(): string
            {
                return 'name';
            }

            public function getSurName(): string
            {
                return 'surname';
            }

            public function getAge(): int
            {
                return 100;
            }
        };
    }

    public function testTransform(): void
    {
        $expectedResult = [
            'name' => 'name',
            'surname' => 'surname',
            'age' => 100
        ];
        $result = $this->transformer->transform('domain', ['name', 'surname', 'age'], $this->object);

        $this->assertSame($expectedResult, $result);
    }
}
