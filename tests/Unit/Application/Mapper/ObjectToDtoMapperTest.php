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

namespace App\Tests\Unit\Application\Mapper;

use App\Application\Mapper\ObjectToDtoMapper;
use Generator;
use App\Tests\Mock\Application\Mapper\{
    MockDto,
    MockEntity,
    MockNestedDto,
    MockNestedEntity,
    MockDtoWithCollection,
    MockEntityWithCollection
};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObjectToDtoMapperTest extends TestCase
{
    private ObjectToDtoMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ObjectToDtoMapper();
    }

    #[DataProvider('provideBasicMappingTestCases')]
    public function testBasicMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    #[DataProvider('provideCollectionMappingTestCases')]
    public function testCollectionMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    #[DataProvider('provideNestedObjectMappingTestCases')]
    public function testNestedObjectMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    public static function provideBasicMappingTestCases(): Generator
    {
        $entity = new MockEntity('John Doe', 30, 'john@example.com');
        $expectedDto = new MockDto('John Doe', 30, 'john@example.com');

        yield 'Basic mapping with all properties' => [
            $entity,
            MockDto::class,
            $expectedDto
        ];

        $entity = new MockEntity('Jane Doe', 25, null);
        $expectedDto = new MockDto('Jane Doe', 25, null);

        yield 'Mapping with null value' => [
            $entity,
            MockDto::class,
            $expectedDto
        ];
    }

    public static function provideCollectionMappingTestCases(): Generator
    {
        $items = [
            new MockEntity('Item 1', 1, 'item1@example.com'),
            new MockEntity('Item 2', 2, 'item2@example.com')
        ];

        $entity = new MockEntityWithCollection(
            'Collection Title',
            $items,
            'Collection Description'
        );

        $expectedItems = [
            new MockDto('Item 1', 1, 'item1@example.com'),
            new MockDto('Item 2', 2, 'item2@example.com')
        ];

        $expectedDto = new MockDtoWithCollection(
            'Collection Title',
            $expectedItems,
            'Collection Description'
        );

        yield 'Mapping collection with MapToDto attribute' => [
            $entity,
            MockDtoWithCollection::class,
            $expectedDto
        ];
    }

    public static function provideNestedObjectMappingTestCases(): Generator
    {
        $nestedEntity = new MockEntity('Nested', 5, 'nested@example.com');
        $entity = new MockNestedEntity('Parent', $nestedEntity);

        $expectedNestedDto = new MockDto('Nested', 5, 'nested@example.com');
        $expectedDto = new MockNestedDto('Parent', $expectedNestedDto);

        yield 'Mapping nested object' => [
            $entity,
            MockNestedDto::class,
            $expectedDto
        ];
    }
}
