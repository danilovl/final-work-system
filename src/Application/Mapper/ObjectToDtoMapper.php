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
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\PropertyAccess\{
    PropertyAccess,
    PropertyAccessor
};

readonly class ObjectToDtoMapper
{
    private PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @throws ReflectionException
     */
    public function map(object $entity, string $dtoClass): object
    {
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

            $value = $this->accessor->getValue($entity, $name);

            if ($value === null) {
                $args[] = null;

                continue;
            }

            if (is_iterable($value)) {
                $property = $dtoReflection->getProperty($name);
                $attributes = $property->getAttributes(MapToDto::class);

                if (count($attributes) > 0) {
                    $attribute = $attributes[0]->newInstance();
                    $targetDtoClass = $attribute->dtoClass;
                    $args[] = $this->mapCollection($value, $targetDtoClass);

                    continue;
                }
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();
                $args[] = $this->map($value, $typeName);

                continue;
            }

            $args[] = $value;
        }

        return $dtoReflection->newInstanceArgs($args);
    }

    /**
     * @throws ReflectionException
     */
    private function mapCollection(iterable $collection, string $dtoClass): array
    {
        $result = [];
        foreach ($collection as $item) {
            $result[] = $this->map($item, $dtoClass);
        }

        return $result;
    }
}
