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

enum AjaxJsonTypeConstant
{
    final public const CREATE_SUCCESS = 'create.success';
    final public const CREATE_FAILURE = 'create.failure';
    final public const SAVE_SUCCESS = 'save.success';
    final public const SAVE_FAILURE = 'save.failure';
    final public const DELETE_SUCCESS = 'delete.success';
    final public const DELETE_FAILURE = 'delete.failure';
}
