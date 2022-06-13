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

enum WorkUserTypeConstant
{
    final public const SUPERVISOR = 'supervisor';
    final public const AUTHOR = 'author';
    final public const OPPONENT = 'opponent';
    final public const CONSULTANT = 'consultant';
}
