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
    public const string USER_CREATE = 'user.create';
    public const string USER_EDIT = 'user.edit';
    public const string WORK_CREATE = 'work.create';
    public const string WORK_EDIT = 'work.edit';
    public const string WORK_REMIND_DEADLINE_CREATE = 'work.remind_deadline.create';
    public const string WORK_AUTHOR_EDIT = 'work.author_edit';
    public const string WORK_VERSION_CREATE = 'work.version.create';
    public const string WORK_VERSION_EDIT = 'work.version.edit';
    public const string TASK_CREATE = 'task.create';
    public const string TASK_EDIT = 'task.edit';
    public const string TASK_COMPLETE = 'task.complete';
    public const string TASK_INCOMPLETE = 'task.incomplete';
    public const string TASK_NOTIFY_COMPLETE = 'task.notify.complete';
    public const string TASK_NOTIFY_INCOMPLETE = 'task.notify.incomplete';
    public const string TASK_REMIND_DEADLINE_CREATE = 'task.remind_deadline.create';
    public const string EVENT_CREATE = 'event.create';
    public const string EVENT_EDIT = 'event.edit';
    public const string EVENT_SWITCH_SKYPE = 'event.switch.skype';
    public const string EVENT_COMMENT_CREATE = 'event.comment.create';
    public const string EVENT_COMMENT_EDIT = 'event.comment.edit';
    public const string EVENT_RESERVATION = 'event.reservation';
    public const string CACHE_CLEAR_KEY = 'cache.clear.key';
    public const string CACHE_CREATE_HOMEPAGE = 'cache.create.homepage';
    public const string ENTITY_POST_PERSIST_FLUSH = 'entity.post_persist_flush';
    public const string MESSAGE_CREATE = 'message.create';
    public const string DOCUMENT_CREATE = 'document.create';
    public const string SECURITY_RESET_PASSWORD_TOKEN = 'security.reset_password.token';
    public const string WIDGET_GROUP_REPLACE = 'widget.group.replace';
}
