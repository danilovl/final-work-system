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

namespace App\Application\Constant;

enum CompareConstant
{
    final public const MORE = '>';
    final public const LESS = '<';
    final public const EQUAL = '===';
    final public const NOT_EQUAL = '!==';
    public const MORE_EQUAL = '>=';
    public const LESS_EQUAL = '<=';
}
