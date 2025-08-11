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

class ObjectHelper
{
    public static function classUsesDeep(object|string $class, bool $autoload = true): array
    {
        $traits = [];

        do {
            /** @var string[] $classUses */
            $classUses = class_uses($class, $autoload);
            $traits = array_merge($classUses, $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            /** @var string[] $classUses */
            $classUses = class_uses($trait, $autoload);
            $traits = array_merge($classUses, $traits);
        }

        return array_values(array_unique($traits));
    }
}
