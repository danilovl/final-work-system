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

namespace App\Domain\SystemEventType\Constant;

enum SystemEventTypeConstant: int
{
    case WORK_CREATE = 1;
    case WORK_EDIT = 2;
    case USER_EDIT = 3;
    case TASK_CREATE = 4;
    case TASK_EDIT = 5;
    case TASK_COMPLETE = 6;
    case TASK_INCOMPLETE = 7;
    case TASK_NOTIFY_COMPLETE = 8;
    case TASK_NOTIFY_INCOMPLETE = 9;
    case TASK_REMIND_DEADLINE = 19;
    case VERSION_CREATE = 10;
    case VERSION_EDIT = 11;
    case DOCUMENT_CREATE = 12;
    case EVENT_CREATE = 13;
    case EVENT_EDIT = 14;
    case EVENT_SWITCH_SKYPE = 15;
    case EVENT_COMMENT_CREATE = 16;
    case EVENT_COMMENT_EDIT = 17;
    case MESSAGE_CREATE = 18;
}
