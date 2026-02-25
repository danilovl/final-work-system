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

use Webmozart\Assert\Assert;

class ArrayMapHelper
{
    /**
     * @template T of object
     * @param array<T> $objects
     * @return int[]
     */
    public static function getObjectsIds(array $objects): array
    {
        Assert::allMethodExists($objects, 'getId');

        /** @var callable(T): int $callback */
        $callback = static function (object $object): int {
            /** @var callable(): int $getId */
            $getId = [$object, 'getId'];

            return $getId();
        };

        return array_map($callback, $objects);
    }
}
