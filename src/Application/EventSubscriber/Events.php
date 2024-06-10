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

namespace App\Application\EventSubscriber;

enum Events
{
    public const USER_CREATE = 'user.create';
    public const USER_EDIT = 'user.edit';

    public const WORK_CREATE = 'work.create';
    public const WORK_EDIT = 'work.edit';
    public const WORK_REMIND_DEADLINE_CREATE = 'work.remind_deadline.create';
    public const WORK_AUTHOR_EDIT = 'work.author_edit';
    public const WORK_VERSION_CREATE = 'work.version.create';
    public const WORK_VERSION_EDIT = 'work.version.edit';

    public const TASK_CREATE = 'task.create';
    public const TASK_EDIT = 'task.edit';
    public const TASK_COMPLETE = 'task.complete';
    public const TASK_INCOMPLETE = 'task.incomplete';
    public const TASK_NOTIFY_COMPLETE = 'task.notify.complete';
    public const TASK_NOTIFY_INCOMPLETE = 'task.notify.incomplete';
    public const TASK_REMIND_DEADLINE_CREATE = 'task.remind_deadline.create';

    public const EVENT_CREATE = 'event.create';
    public const EVENT_EDIT = 'event.edit';
    public const EVENT_SWITCH_SKYPE = 'event.switch.skype';
    public const EVENT_COMMENT_CREATE = 'event.comment.create';
    public const EVENT_COMMENT_EDIT = 'event.comment.edit';
    public const EVENT_RESERVATION = 'event.reservation';

    public const CACHE_CLEAR_KEY = 'cache.clear.key';
    public const CACHE_CREATE_HOMEPAGE = 'cache.create.homepage';

    public const MESSAGE_CREATE = 'message.create';
    public const DOCUMENT_CREATE = 'document.create';
    public const SECURITY_RESET_PASSWORD_TOKEN = 'security.reset_password.token';
    public const WIDGET_GROUP_REPLACE = 'widget.group.replace';
}
