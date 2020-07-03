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

class SystemEventTypeConstant
{
    public const WORK_CREATE = 1;
    public const WORK_EDIT = 2;
    public const USER_EDIT = 3;
    public const TASK_CREATE = 4;
    public const TASK_EDIT = 5;
    public const TASK_COMPLETE = 6;
    public const TASK_INCOMPLETE = 7;
    public const TASK_NOTIFY_COMPLETE = 8;
    public const TASK_NOTIFY_INCOMPLETE = 9;
    public const VERSION_CREATE = 10;
    public const VERSION_EDIT = 11;
    public const DOCUMENT_CREATE = 12;
    public const EVENT_CREATE = 13;
    public const EVENT_EDIT = 14;
    public const EVENT_SWITCH_SKYPE = 15;
    public const EVENT_COMMENT_CREATE = 16;
    public const EVENT_COMMENT_EDIT = 17;
    public const MESSAGE_CREATE = 18;
}