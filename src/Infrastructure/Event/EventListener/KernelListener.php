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

namespace App\Infrastructure\Event\EventListener;

use App\Application\Attribute\EntityRelationValidatorAttribute;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class KernelListener implements EventSubscriberInterface
{
    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        /** @var array{object, string} $controller */
        [$controllerObject, $methodName] = $controller;
        $reflectionMethod = new ReflectionMethod($controllerObject, $methodName);

        $attributes = $reflectionMethod->getAttributes(EntityRelationValidatorAttribute::class);
        if (count($attributes) === 0) {
            return;
        }

        $arguments = $event->getArguments();

        $attribute = $attributes[0];
        /** @var EntityRelationValidatorAttribute $entityRelationValidator */
        $entityRelationValidator = $attribute->newInstance();

        $sourceEntityClass = $entityRelationValidator->sourceEntity;
        $targetEntityClass = $entityRelationValidator->targetEntity;

        $sourceEntity = null;
        $targetEntity = null;

        foreach ($arguments as $argument) {
            if (is_object($argument) && is_a($argument, $sourceEntityClass)) {
                $sourceEntity = $argument;
            }

            if (is_object($argument) && is_a($argument, $targetEntityClass)) {
                $targetEntity = $argument;
            }
        }

        if ($sourceEntity === null || $targetEntity === null) {
            return;
        }

        $relationProperty = $this->findRelationProperty($sourceEntity, $targetEntityClass, $entityRelationValidator->property);

        $relatedValue = $relationProperty->isInitialized($sourceEntity) ? $relationProperty->getValue($sourceEntity) : null;
        if (is_iterable($relatedValue)) {
            $relatedValue = $this->findInCollection($relatedValue, $targetEntity);
        }

        $relatedEntity = is_object($relatedValue) && is_a($relatedValue, $targetEntityClass) ? $relatedValue : null;

        if ($relatedEntity === null) {
            $message = sprintf(
                'Entity "%s" is not related to entity "%s": related entity is null.',
                $sourceEntity::class,
                $targetEntity::class
            );

            throw new BadRequestException($message);
        }

        if (!method_exists($relatedEntity, 'getId') || !method_exists($targetEntity, 'getId')) {
            throw new BadRequestException('Method "getId" not found.');
        }

        $relatedEntityId = $relatedEntity->getId();
        $targetEntityId = $targetEntity->getId();

        if ($relatedEntityId !== $targetEntityId) {
            $message = sprintf(
                'Entity "%s" is not related to entity "%s": IDs do not match (%s !== %s).',
                get_class($sourceEntity),
                get_class($targetEntity),
                $relatedEntityId,
                $targetEntityId
            );

            throw new BadRequestException($message);
        }
    }

    private function findRelationProperty(object $sourceEntity, string $targetEntityClass, ?string $propertyName): ReflectionProperty
    {
        $sourceReflection = new ReflectionClass($sourceEntity);

        if ($propertyName !== null) {
            if (!$sourceReflection->hasProperty($propertyName)) {
                $message = sprintf('Property "%s" not found in "%s".', $propertyName, $sourceEntity::class);

                throw new LogicException($message);
            }

            return $sourceReflection->getProperty($propertyName);
        }

        $properties = $sourceReflection->getProperties();
        $filteredProperties = array_filter(
            $properties,
            fn (ReflectionProperty $property): bool => $this->acceptsClass($property, $targetEntityClass)
        );
        $candidates = array_values($filteredProperties);

        if (count($candidates) === 0) {
            $message = sprintf('Entity "%s" has no property of type "%s".', $sourceEntity::class, $targetEntityClass);

            throw new LogicException($message);
        }

        if (count($candidates) > 1) {
            $names = array_map(static fn (ReflectionProperty $property): string => $property->getName(), $candidates);
            $message = sprintf(
                'Entity "%s" has several properties of type "%s" (%s), set "property" in %s.',
                $sourceEntity::class,
                $targetEntityClass,
                implode(', ', $names),
                EntityRelationValidatorAttribute::class
            );

            throw new LogicException($message);
        }

        return $candidates[0];
    }

    /**
     * @param iterable<mixed> $collection
     */
    private function findInCollection(iterable $collection, object $targetEntity): ?object
    {
        if (!method_exists($targetEntity, 'getId')) {
            return null;
        }

        $items = is_array($collection) ? $collection : iterator_to_array($collection, false);

        /** @var object|null $item */
        $item = array_find(
            $items,
            static fn (mixed $item): bool => is_object($item) &&
                is_a($item, $targetEntity::class) &&
                method_exists($item, 'getId') &&
                $item->getId() === $targetEntity->getId()
        );

        return $item;
    }

    private function acceptsClass(ReflectionProperty $property, string $class): bool
    {
        $type = $property->getType();
        $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];

        return array_any(
            $types,
            static fn (mixed $namedType): bool => $namedType instanceof ReflectionNamedType &&
                !$namedType->isBuiltin() &&
                is_a($class, $namedType->getName(), true)
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [['onKernelController', -1]]
        ];
    }
}
