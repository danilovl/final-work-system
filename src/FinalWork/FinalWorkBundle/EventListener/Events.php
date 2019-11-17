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

namespace FinalWork\FinalWorkBundle\EventListener;

final class Events
{
    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    public const NOTIFICATION_WORK_CREATE = 'notification.work.create';
    public const NOTIFICATION_WORK_EDIT = 'notification.work.edit';
    public const NOTIFICATION_TASK_CREATE = 'notification.task.create';
    public const NOTIFICATION_TASK_EDIT = 'notification.task.edit';
    public const NOTIFICATION_TASK_COMPLETE = 'notification.task.complete';
    public const NOTIFICATION_TASK_INCOMPLETE = 'notification.task.incomplete';
    public const NOTIFICATION_TASK_NOTIFY_COMPLETE = 'notification.task.notify.complete';
    public const NOTIFICATION_TASK_NOTIFY_INCOMPLETE = 'notification.task.notify.incomplete';
    public const NOTIFICATION_VERSION_CREATE = 'notification.work.version.create';
    public const NOTIFICATION_VERSION_EDIT = 'notification.work.version.edit';
    public const NOTIFICATION_DOCUMENT_CREATE = 'notification.document.create';
    public const NOTIFICATION_EVENT_CREATE = 'notification.event.create';
    public const NOTIFICATION_EVENT_EDIT = 'notification.event.edit';
    public const NOTIFICATION_EVENT_SWITCH_SKYPE = 'notification.event.switch.skype';
    public const NOTIFICATION_EVENT_COMMENT_CREATE = 'notification.event.comment.create';
    public const NOTIFICATION_EVENT_COMMENT_EDIT = 'notification.event.comment.edit';
    public const NOTIFICATION_EVENT_RESERVATION = 'notification.event.reservation';
    public const NOTIFICATION_MESSAGE_CREATE = 'notification.message.create';
    public const NOTIFICATION_USER_CREATE = 'notification.user.create';
    public const NOTIFICATION_USER_EDIT = 'notification.user.edit';

    /**
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    public const SYSTEM_WORK_CREATE = 'system.work.create';
    public const SYSTEM_WORK_EDIT = 'system.work.edit';
    public const SYSTEM_WORK_AUTHOR_EDIT = 'system.work.author_edit';
    public const SYSTEM_USER_EDIT = 'system.user.edit';
    public const SYSTEM_TASK_CREATE = 'system.task.create';
    public const SYSTEM_TASK_EDIT = 'system.task.edit';
    public const SYSTEM_TASK_COMPLETE = 'system.task.complete';
    public const SYSTEM_TASK_INCOMPLETE = 'system.task.incomplete';
    public const SYSTEM_TASK_NOTIFY_COMPLETE = 'system.task.notify.complete';
    public const SYSTEM_TASK_NOTIFY_INCOMPLETE = 'system.task.notify.incomplete';
    public const SYSTEM_VERSION_CREATE = 'system.work.version.create';
    public const SYSTEM_VERSION_EDIT = 'system.work.version.edit';
    public const SYSTEM_DOCUMENT_CREATE = 'system.document.create';
    public const SYSTEM_EVENT_CREATE = 'system.event.create';
    public const SYSTEM_EVENT_EDIT = 'system.event.edit';
    public const SYSTEM_EVENT_SWITCH_SKYPE = 'system.event.switch.skype';
    public const SYSTEM_EVENT_COMMENT_CREATE = 'system.event.comment.create';
    public const SYSTEM_EVENT_COMMENT_EDIT = 'system.event.comment.edit';
    public const SYSTEM_EVENT_RESERVATION = 'system.event.reservation';
    public const SYSTEM_MESSAGE_CREATE = 'system.message.create';
}
