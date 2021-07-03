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

namespace App\DataTransferObject;

use App\Exception\PropertyNotExistException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseDataTransferObject implements DataTransferObjectInterface
{
    public static function createFromArray(
        array $params,
        array|bool $requiredParamNames = true
    ): static {
        $dataTransferObject = new static;

        foreach ($params as $key => $value) {
            static::setValue($dataTransferObject, $key, $value);
        }

        $requiredParamNames = is_array($requiredParamNames) ? $requiredParamNames : ($requiredParamNames === true ? array_keys($params) : null);
        if ($requiredParamNames === null) {
            return $dataTransferObject;
        }

        $resolver = new OptionsResolver;
        static::configureResolver($resolver, $requiredParamNames);
        $resolver->resolve($params);

        return $dataTransferObject;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
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
