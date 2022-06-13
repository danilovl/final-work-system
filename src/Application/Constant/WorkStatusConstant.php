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

enum WorkStatusConstant
{
    final public const ACTIVE = 1;
    final public const ARCHIVE = 2;
    final public const AUXILIARY = 3;
    final public const PRELIMINARY = 4;
    final public const UNCLASSIFIED = 5;
}
