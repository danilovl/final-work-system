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
    MockEntityWithCollection,
    MockDtoWithoutConstructor,
    MockEntityWithNonReadableProperty,
    MockDtoForNonReadableTest,
    MockDtoWithDefaultValue
};
use RuntimeException;
use Webmozart\Assert\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObjectToDtoMapperTest extends TestCase
{
    private ObjectToDtoMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ObjectToDtoMapper;
    }

    public function testConstructorException(): void
    {
        $entity = new MockEntity('John Doe', 30, 'john@example.com');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DTO class must have a constructor: App\Tests\Mock\Application\Mapper\MockDtoWithoutConstructor');

        $this->mapper->map($entity, MockDtoWithoutConstructor::class);
    }

    #[DataProvider('provideBasicMappingCases')]
    public function testBasicMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    #[DataProvider('provideCollectionMappingCases')]
    public function testCollectionMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    #[DataProvider('provideNestedObjectMappingCases')]
    public function testNestedObjectMapping(object $entity, string $dtoClass, object $expectedDto): void
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    public static function provideBasicMappingCases(): Generator
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

    public static function provideCollectionMappingCases(): Generator
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

    public static function provideNestedObjectMappingCases(): Generator
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

    #[DataProvider('provideNonReadablePropertyCases')]
    public function testNonReadableProperty(object $entity, string $dtoClass, object $expectedDto): void
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $result = $this->mapper->map($entity, $dtoClass);

        $this->assertEquals($expectedDto, $result);
    }

    public static function provideNonReadablePropertyCases(): Generator
    {
        $entity = new MockEntityWithNonReadableProperty('John Doe', 30, 'john@example.com');
        $expectedDto = new MockDtoForNonReadableTest('John Doe', 30, null);

        yield 'Mapping with non-readable property' => [
            $entity,
            MockDtoForNonReadableTest::class,
            $expectedDto
        ];

        $entity = new MockEntityWithNonReadableProperty('Jane Doe', 25, 'jane@example.com');
        $expectedDto = new MockDtoWithDefaultValue('Jane Doe', 25, 'default@example.com');

        yield 'Mapping with non-readable property using default value' => [
            $entity,
            MockDtoWithDefaultValue::class,
            $expectedDto
        ];
    }
}
