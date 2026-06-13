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

namespace App\Application\Mapper;

use App\Application\Mapper\Attribute\MapToDto;
use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\PropertyAccess\{
    PropertyAccess,
    PropertyAccessor
};
use Symfony\Component\Serializer\Attribute\Groups;
use Webmozart\Assert\Assert;

readonly class ObjectToDtoMapper
{
    private PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @template T of object
     * @param object $entity
     * @param class-string<T> $dtoClass
     * @param string[] $ignoreGroups
     * @return T
     * @throws ReflectionException
     */
    public function map(object $entity, string $dtoClass, array $ignoreGroups = []): object
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $dtoReflection = new ReflectionClass($dtoClass);
        $constructor = $dtoReflection->getConstructor();

        if (!$constructor) {
            $message = sprintf('DTO class must have a constructor: %s', $dtoClass);

            throw new RuntimeException($message);
        }

        $args = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (!$this->accessor->isReadable($entity, $name)) {
                $args[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                continue;
            }

            $entityValue = $this->accessor->getValue($entity, $name);
            $property = $dtoReflection->getProperty($name);

            if (!empty($ignoreGroups) && $type instanceof ReflectionNamedType && $type->allowsNull()) {
                $groupAttrs = $property->getAttributes(Groups::class);
                $propertyGroups = [];
                foreach ($groupAttrs as $attr) {
                    $propertyGroups = array_merge($propertyGroups, $attr->newInstance()->getGroups());
                }
                if (!empty(array_intersect($ignoreGroups, $propertyGroups))) {
                    $args[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                    continue;
                }
            }

            if ($entityValue === null) {
                if ($type instanceof ReflectionNamedType && !$type->allowsNull()) {
                    $message = sprintf('Non-nullable property "%s" of "%s" is null', $name, $dtoClass);

                    throw new RuntimeException($message);
                }
                $args[] = null;

                continue;
            }

            if ($entityValue instanceof DateTimeInterface) {
                $args[] = $entityValue->format(DateTimeInterface::ATOM);

                continue;
            }

            if (is_iterable($entityValue)) {
                $property = $dtoReflection->getProperty($name);
                $attributes = $property->getAttributes(MapToDto::class);

                if (count($attributes) > 0) {
                    $attribute = $attributes[0]->newInstance();
                    /** @var class-string $targetDtoClass */
                    $targetDtoClass = $attribute->dtoClass;
                    $args[] = $this->mapCollection($entityValue, $targetDtoClass, $ignoreGroups);

                    continue;
                }
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                /** @var class-string $typeName */
                $typeName = $type->getName();

                if (is_object($entityValue)) {
                    $args[] = $this->map($entityValue, $typeName, $ignoreGroups);
                }

                continue;
            }

            $args[] = $entityValue;
        }

        return $dtoReflection->newInstanceArgs($args);
    }

    /**
     * @template T of object
     * @param iterable<object|mixed> $collection
     * @param class-string<T> $dtoClass
     * @param string[] $ignoreGroups
     * @return array<T>
     * @throws ReflectionException
     */
    private function mapCollection(iterable $collection, string $dtoClass, array $ignoreGroups = []): array
    {
        Assert::classExists($dtoClass, 'DTO class must exist: %s');

        $result = [];
        foreach ($collection as $item) {
            if (is_object($item)) {
                $result[] = $this->map($item, $dtoClass, $ignoreGroups);
            }
        }

        return $result;
    }
}
