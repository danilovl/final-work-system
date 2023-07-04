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

enum WorkStatusConstant: int
{
    case ACTIVE = 1;
    case ARCHIVE = 2;
    case AUXILIARY = 3;
    case PRELIMINARY = 4;
    case UNCLASSIFIED = 5;
}
