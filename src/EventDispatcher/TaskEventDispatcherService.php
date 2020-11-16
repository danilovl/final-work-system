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

namespace App\EventDispatcher;

use App\Constant\TaskStatusConstant;
use App\Entity\Task;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class TaskEventDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onTaskCreate(Task $task): void
    {
        $genericEvent = new GenericEvent($task);

        if ($task->isActive()) {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_CREATE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_CREATE);
        }
    }

    public function onTaskEdit(Task $task): void
    {
        $genericEvent = new GenericEvent($task);

        if ($task->isActive()) {
            if ($task->getSystemEvents()) {
                $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_EDIT);
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_EDIT);
            } else {
                $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_CREATE);
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_CREATE);
            }
        }
    }

    public function onTaskNotifyComplete(Task $task): void
    {
        $genericEvent = new GenericEvent($task);

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_NOTIFY_COMPLETE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_NOTIFY_COMPLETE);
    }

    public function onTaskChangeStatus(Task $task, string $type): void
    {
        switch ($type) {
            case TaskStatusConstant::ACTIVE:
                $genericEvent = new GenericEvent($task);
                $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_CREATE);
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_CREATE);
                break;
            case TaskStatusConstant::COMPLETE:
                $genericEvent = new GenericEvent($task);
                if ($task->isComplete()) {
                    $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_COMPLETE);
                    $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_COMPLETE);
                } else {
                    $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_INCOMPLETE);
                    $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_INCOMPLETE);
                }
                break;
            case TaskStatusConstant::NOTIFY:
                $genericEvent = new GenericEvent($task);
                $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE);
                $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_NOTIFY_INCOMPLETE);
                break;
        }
    }

    public function onTaskReminderCreate(Task $task): void
    {
        $genericEvent = new GenericEvent($task);

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_REMIND_DEADLINE_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_REMIND_CREATE);
    }
}
