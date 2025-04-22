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

use App\Application\Exception\RuntimeException;
use Doctrine\ORM\Mapping\Table;
use ReflectionClass;

class AttributeHelper
{
    public static function getEntityTableName(string $entity): string
    {
        $reflectionClass = new ReflectionClass($entity);
        $attributes = $reflectionClass->getAttributes(Table::class);

        if (empty($attributes)) {
            throw new RuntimeException(sprintf('Attribute %s not found.', Table::class));
        }

        /** @var Table $attribute */
        $attribute = $attributes[0]->newInstance();
        /** @var string $name */
        $name = $attribute->name;

        return $name;
    }
}
