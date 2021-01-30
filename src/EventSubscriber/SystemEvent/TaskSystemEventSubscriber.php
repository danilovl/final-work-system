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

namespace App\EventSubscriber\SystemEvent;

use App\EventDispatcher\GenericEvent\TaskGenericEvent;
use App\EventSubscriber\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};

class TaskSystemEventSubscriber extends BaseSystemEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_TASK_CREATE => 'onTaskCreate',
            Events::SYSTEM_TASK_EDIT => 'onTaskEdit',
            Events::SYSTEM_TASK_COMPLETE => 'onTaskComplete',
            Events::SYSTEM_TASK_INCOMPLETE => 'onTaskInComplete',
            Events::SYSTEM_TASK_NOTIFY_COMPLETE => 'onTaskNotifyComplete',
            Events::SYSTEM_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete',
            Events::NOTIFICATION_TASK_REMIND_DEADLINE_CREATE => 'onTaskReminderDeadlineCreate'
        ];
    }

    private function baseEvent(
        TaskGenericEvent $event,
        int $systemEventId,
        bool $recipientAuthor = true
    ): void {
        $task = $event->task;
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($recipientAuthor ? $task->getOwner() : $work->getAuthor());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find($systemEventId)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($recipientAuthor ? $work->getAuthor() : $task->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_CREATE);
    }

    public function onTaskEdit(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_EDIT);
    }

    public function onTaskComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_COMPLETE);
    }

    public function onTaskInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_INCOMPLETE);
    }

    public function onTaskNotifyComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_COMPLETE, false);
    }

    public function onTaskNotifyInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE);
    }

    public function onTaskReminderDeadlineCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_REMIND_DEADLINE);
    }
}
