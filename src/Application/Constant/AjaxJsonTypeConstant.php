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

namespace App\Application\Constant;

enum AjaxJsonTypeConstant: string
{
    case CREATE_SUCCESS = 'create.success';
    case CREATE_FAILURE = 'create.failure';
    case SAVE_SUCCESS = 'save.success';
    case SAVE_FAILURE = 'save.failure';
    case DELETE_SUCCESS = 'delete.success';
    case DELETE_FAILURE = 'delete.failure';
}
