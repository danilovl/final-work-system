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

namespace App\Constant;

class ControllerMethodConstant
{
    public const CREATE = 'create';
    public const CREATE_AJAX = 'create.ajax';
    public const CREATE_SEVERAL = 'create_several';
    public const CREATE_SEVERAL_AJAX = 'create_several.ajax';
    public const EDIT = 'edit';
    public const EDIT_AJAX = 'edit.ajax';
    public const LIST = 'list';
    public const LIST_OWNER = 'list.owner';
}