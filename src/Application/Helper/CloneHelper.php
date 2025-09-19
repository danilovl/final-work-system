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

class CloneHelper
{
    public static function simpleCloneObject(object $object): object
    {
        /** @var object $object */
        $object = unserialize(serialize($object));

        return $object;
    }

    /**
     * @param object[] $objects
     * @return object[]
     */
    public static function simpleCloneObjects(array $objects): array
    {
        return array_map(static fn (object $object): object => self::simpleCloneObject($object), $objects);
    }
}
