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

namespace App\Application\Helper;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\{
    ObjectNormalizer,
    DateTimeNormalizer
};

class SerializerHelper
{
    public static function getBaseSerializer(): Serializer
    {
        return new Serializer([new DateTimeNormalizer, new ObjectNormalizer], [new JsonEncoder]);
    }

    /**
     * @template T of object
     * @param class-string<T> $toClass
     * @return T
     */
    public static function convertToObject(
        object $object,
        string $toClass,
        array $serializeContext = [],
        array $deserializeContext = [],
    ): object {
        $serializer = self::getBaseSerializer();
        $jsonContent = $serializer->serialize(
            data: $object,
            format: 'json',
            context: $serializeContext
        );

        return $serializer->deserialize(
            data: $jsonContent,
            type: $toClass,
            format: 'json',
            context: $deserializeContext
        );
    }
}
