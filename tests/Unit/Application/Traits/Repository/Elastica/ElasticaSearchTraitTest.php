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

namespace App\Tests\Unit\Application\Traits\Repository\Elastica;

use App\Tests\Mock\Application\Traits\Repository\Elastica\ElasticaSearchMock;
use Elastica\Result;
use PHPUnit\Framework\TestCase;
use stdClass;
use Webmozart\Assert\InvalidArgumentException;

class ElasticaSearchTraitTest extends TestCase
{
    private ElasticaSearchMock $mockObject;

    protected function setUp(): void
    {
        $this->mockObject = new ElasticaSearchMock;
    }

    public function testTransformSearch(): void
    {
        $this->assertSame('some search text', $this->mockObject->testTransformSearch('Some SEARCH TeXt'));
        $this->assertSame('какой-то текст для поиска', $this->mockObject->testTransformSearch('какой-ТО ТЕКСТ для поиска'));
        $this->assertSame('nějaký text hledání', $this->mockObject->testTransformSearch('NĚJAKÝ text hledání'));
    }

    public function testGetDocumentIds(): void
    {
        $resultMock1 = $this->createMock(Result::class);
        $resultMock1
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $resultMock2 = $this->createMock(Result::class);
        $resultMock2
            ->expects($this->once())
            ->method('getId')
            ->willReturn(10);

        $resultMock3 = $this->createMock(Result::class);
        $resultMock3
            ->expects($this->once())
            ->method('getId')
            ->willReturn(100);

        $results = [$resultMock1, $resultMock2, $resultMock3];

        $this->assertSame([1, 10, 100], $this->mockObject->testGetDocumentIds($results));
    }

    public function testGetDocumentIdsExceptionClass(): void
    {
        $resultMock1 = $this->createMock(Result::class);
        $resultMock2 = $this->createMock(Result::class);
        $resultMock3 = new stdClass;

        $results = [$resultMock1, $resultMock2, $resultMock3];

        $this->expectException(InvalidArgumentException::class);

        $this->mockObject->testGetDocumentIds($results);
    }
}
