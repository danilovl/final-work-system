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

namespace App\EventListener\SystemEvent;

use App\EventListener\Events;
use App\Constant\SystemEventTypeConstant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\{
    Task,
    SystemEvent,
    SystemEventType,
    SystemEventRecipient
};
use Symfony\Component\EventDispatcher\GenericEvent;

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
        GenericEvent $event,
        int $systemEventId,
        bool $recipientAuthor = true

    ): void {
        /** @var Task $task */
        $task = $event->getSubject();
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

    public function onTaskCreate(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_CREATE);
    }

    public function onTaskEdit(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_EDIT);
    }

    public function onTaskComplete(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_COMPLETE);
    }

    public function onTaskInComplete(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_INCOMPLETE);
    }

    public function onTaskNotifyComplete(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_COMPLETE, false);
    }

    public function onTaskNotifyInComplete(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE);
    }

    public function onTaskReminderDeadlineCreate(GenericEvent $event): void
    {
        $this->baseEvent($event, SystemEventTypeConstant::TASK_REMIND_DEADLINE);
    }
}
