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

namespace App\EventListener\EmailNotification;

use App\Entity\Task;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

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
            Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete'
        ];
    }

    public function onTaskCreate(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_create');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_create'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onTaskEdit(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_edit');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_edit'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onTaskComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_complete');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_complete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onTaskInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_in_complete');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_incomplete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onTaskNotifyComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_notify_complete');
        $to = $work->getSupervisor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_notify_complete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }

    public function onTaskNotifyInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        $work = $task->getWork();

        $subject = $this->trans('subject.task_notify_in_complete');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_notify_incomplete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->addEmailNotificationToQueue($subject, $to, $this->sender, $body);
    }
}