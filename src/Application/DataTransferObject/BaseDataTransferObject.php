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

namespace App\Application\DataTransferObject;

use App\Application\Exception\PropertyNotExistException;
use App\Application\Interfaces\DataTransferObject\DataTransferObjectInterface;
use ReflectionClass;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseDataTransferObject implements DataTransferObjectInterface
{
    public static function createFromArray(
        array $params,
        array|bool $requiredParamNames = true
    ): static {
        $requiredParamNames = is_array($requiredParamNames) ? $requiredParamNames : ($requiredParamNames === true ? array_keys($params) : null);
        if ($requiredParamNames !== null) {
            $resolver = new OptionsResolver;
            static::configureResolver($resolver, $requiredParamNames);
            $resolver->resolve($params);
        }

        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            $dataTransferObject = new static(...$params);
        } else {
            $dataTransferObject = new static;

            foreach ($params as $key => $value) {
                static::setValue($dataTransferObject, $key, $value);
            }
        }

        return $dataTransferObject;
    }

    public static function createFromJson(
        string $json,
        array|bool $requiredParamNames = true
    ): static {
        /** @var array $params */
        $params = json_decode($json, true);

        return static::createFromArray($params, $requiredParamNames);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toJson(): string|false
    {
        return json_encode($this->toArray());
    }

    protected static function setValue(
        object $object,
        string $key,
        mixed $value
    ): void {
        if (!property_exists($object, $key)) {
            throw new PropertyNotExistException;
        }

        $object->$key = $value;
    }

    public static function configureResolver(
        OptionsResolver $resolver,
        array $requiredOptionNames
    ): void {
        $resolver->setRequired($requiredOptionNames);
    }
}
