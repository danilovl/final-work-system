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

enum VoterSupportConstant: string
{
   case CREATE = 'create';
   case VIEW = 'view';
   case EDIT = 'edit';
   case CLONE = 'clone';
   case DELETE = 'delete';
   case CHANGE_VIEWED = 'change.viewed';
   case SWITCH_TO_SKYPE = 'switch.to.skype';
   case RESERVATION = 'reservation';
   case DOWNLOAD = 'download';
   case CHANGE_READ_MESSAGE_STATUS = 'change.read.message.status';
   case TASK_NOTIFY_COMPLETE = 'task.notify_complete';
}
