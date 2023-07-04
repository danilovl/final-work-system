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

enum CompareConstant: string
{
    case MORE = '>';
    case LESS = '<';
    case EQUAL = '===';
    case NOT_EQUAL = '!==';
    case MORE_EQUAL = '>=';
    case LESS_EQUAL = '<=';
}
