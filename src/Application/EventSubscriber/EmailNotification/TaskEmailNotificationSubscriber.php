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

namespace App\Application\EventSubscriber\EmailNotification;

use App\Application\DataTransferObject\EventSubscriber\EmailNotificationToQueueData;
use App\Application\EventSubscriber\Events;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaskEmailNotificationSubscriber extends BaseEmailNotificationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::NOTIFICATION_TASK_CREATE => 'onTaskCreate',
            Events::NOTIFICATION_TASK_EDIT => 'onTaskEdit',
            Events::NOTIFICATION_TASK_COMPLETE => 'onTaskComplete',
            Events::NOTIFICATION_TASK_INCOMPLETE => 'onTaskInComplete',
            Events::NOTIFICATION_TASK_NOTIFY_COMPLETE => 'onTaskNotifyComplete',
            Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete',
            Events::NOTIFICATION_TASK_REMIND_DEADLINE_CREATE => 'onTaskReminderDeadlineCreate'
        ];
    }

    private function baseEvent(
        TaskGenericEvent $event,
        string $subject,
        string $template,
        bool $isAuthorEmail = true
    ): void {
        $task = $event->task;
        $work = $task->getWork();

        $subject = $this->trans($subject);
        $to = $isAuthorEmail ? $work->getAuthor()->getEmail() : $work->getSupervisor()->getEmail();
        $templateParameters = [
            'taskOwner' => $task->getOwner()->getFullNameDegree(),
            'taskName' => $task->getName(),
            'workId' => $work->getId(),
            'workTitle' => $work->getTitle(),
            'workAuthor' => $work->getAuthor()->getFullNameDegree()
        ];

        $emailNotificationToQueueData = EmailNotificationToQueueData::createFromArray([
            'locale' => $this->locale,
            'subject' => $subject,
            'to' => $to,
            'from' => $this->sender,
            'template' => $template,
            'templateParameters' => $templateParameters
        ]);

        $this->addEmailNotificationToQueue($emailNotificationToQueueData);
    }

    public function onTaskCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_create', 'task_create');
    }

    public function onTaskEdit(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_edit', 'task_edit');
    }

    public function onTaskComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_complete', 'task_complete');
    }

    public function onTaskInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_in_complete', 'task_incomplete');
    }

    public function onTaskNotifyComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_notify_complete', 'task_notify_complete', false);
    }

    public function onTaskNotifyInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_notify_in_complete', 'task_notify_incomplete');
    }

    public function onTaskReminderDeadlineCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, 'subject.task_reminder_deadline', 'task_reminder_deadline');
    }
}
