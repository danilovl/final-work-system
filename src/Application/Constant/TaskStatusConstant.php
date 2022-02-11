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

enum TaskStatusConstant
{
    public const ACTIVE = 'active';
    public const COMPLETE = 'complete';
    public const NOTIFY = 'notify';
}
