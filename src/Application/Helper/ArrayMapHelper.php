<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Helper;

class ArrayMapHelper
{
    /**
     * @param object[] $objects
     * @return int[]
     */
    public static function getObjectsIds(array $objects): array
    {
        $objects = array_filter($objects, function (object $object): bool {
            return method_exists($object, 'getId');
        });

        return array_map(static fn(object $object): int => $object->getId(), $objects);
    }
}
