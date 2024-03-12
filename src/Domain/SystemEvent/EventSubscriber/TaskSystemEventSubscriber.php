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

namespace App\Domain\SystemEvent\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use App\Domain\SystemEventType\Entity\SystemEventType;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

        /** @var SystemEventType $systemEventType */
        $systemEventType = $this->entityManagerService
            ->getRepository(SystemEventType::class)
            ->find($systemEventId);

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($recipientAuthor ? $task->getOwner() : $work->getAuthor());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($systemEventType);

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($recipientAuthor ? $work->getAuthor() : $task->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->entityManagerService->persistAndFlush($systemEvent);
    }

    public function onTaskCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_CREATE->value);
    }

    public function onTaskEdit(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_EDIT->value);
    }

    public function onTaskComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_COMPLETE->value);
    }

    public function onTaskInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_INCOMPLETE->value);
    }

    public function onTaskNotifyComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_COMPLETE->value, false);
    }

    public function onTaskNotifyInComplete(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE->value);
    }

    public function onTaskReminderDeadlineCreate(TaskGenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_REMIND_DEADLINE->value);
    }
}
