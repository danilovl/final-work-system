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

enum VoterSupportConstant
{
    final public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const CLONE = 'clone';
    public const DELETE = 'delete';
    public const CHANGE_VIEWED = 'change.viewed';
    public const SWITCH_TO_SKYPE = 'switch.to.skype';
    public const RESERVATION = 'reservation';
    public const DOWNLOAD = 'download';
    public const CHANGE_READ_MESSAGE_STATUS = 'change.read.message.status';
    public const TASK_NOTIFY_COMPLETE = 'task.notify_complete';
}
