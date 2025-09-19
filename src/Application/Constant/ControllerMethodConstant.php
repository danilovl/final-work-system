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

enum ControllerMethodConstant: string
{
    case CREATE = 'create';
    case CREATE_AJAX = 'create.ajax';
    case CREATE_SEVERAL = 'create_several';
    case CREATE_SEVERAL_AJAX = 'create_several.ajax';
    case EDIT = 'edit';
    case EDIT_AJAX = 'edit.ajax';
    case LIST = 'list';
    case LIST_OWNER = 'list.owner';
}
