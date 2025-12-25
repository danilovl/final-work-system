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
            throw new RuntimeException("DTO class must have a constructor: $dtoClass");
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (!$this->accessor->isReadable($entity, $name)) {
                $args[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                continue;
            }

            $value = $this->accessor->getValue($entity, $name);

            if ($value === null) {
                $args[] = null;
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();

                if (str_ends_with($typeName, 'DTO')) {
                    $args[] = $this->map($value, $typeName);

                    continue;
                }
            }

            $args[] = $value;
        }

        return $dtoReflection->newInstanceArgs($args);
    }
}
