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
use ReflectionClass;
use ReflectionMethod;
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
            if (is_a($argument, $sourceEntityClass)) {
                $sourceEntity = $argument;
            }

            if (is_a($argument, $targetEntityClass)) {
                $targetEntity = $argument;
            }
        }

        if ($sourceEntity === null || $targetEntity === null) {
            return;
        }

        $sourceReflection = new ReflectionClass($sourceEntity);
        $sourceProperties = $sourceReflection->getProperties();

        $relatedEntity = null;

        foreach ($sourceProperties as $property) {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($sourceEntity);

            if (is_object($propertyValue) && is_a($propertyValue, $targetEntityClass)) {
                $relatedEntity = $propertyValue;

                break;
            }
        }

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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [['onKernelController', -1]]
        ];
    }
}
