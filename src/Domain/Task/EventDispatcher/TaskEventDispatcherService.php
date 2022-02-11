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

namespace App\Domain\Task\EventDispatcher;

use App\Application\Constant\TaskStatusConstant;
use App\Application\EventSubscriber\Events;
use App\Application\Exception\RuntimeException;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaskEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onTaskCreate(Task $task): void
    {
        $genericEvent = new TaskGenericEvent;
        $genericEvent->task = $task;

        if (!$task->isActive()) {
            return;
        }

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_CREATE);
    }

    public function onTaskEdit(Task $task): void
    {
        $genericEvent = new TaskGenericEvent;
        $genericEvent->task = $task;

        if (!$task->isActive()) {
            return;
        }

        $this->eventDispatcher->dispatch(
            $genericEvent,
            !$task->getSystemEvents()->isEmpty() ? Events::NOTIFICATION_TASK_EDIT : Events::NOTIFICATION_TASK_CREATE
        );

        $this->eventDispatcher->dispatch(
            $genericEvent,
            !$task->getSystemEvents()->isEmpty() ? Events::SYSTEM_TASK_EDIT : Events::SYSTEM_TASK_CREATE
        );
    }

    public function onTaskNotifyComplete(Task $task): void
    {
        $genericEvent = new TaskGenericEvent;
        $genericEvent->task = $task;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_NOTIFY_COMPLETE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_NOTIFY_COMPLETE);
    }

    public function onTaskChangeStatus(Task $task, string $type): void
    {
        $genericEvent = new TaskGenericEvent;
        $genericEvent->task = $task;
        $genericEvent->type = $type;

        switch ($type) {
            case TaskStatusConstant::ACTIVE:
                $notificationEvent = Events::NOTIFICATION_TASK_CREATE;
                $systemEvent = Events::SYSTEM_TASK_CREATE;
                break;
            case TaskStatusConstant::COMPLETE:
                $notificationEvent = Events::NOTIFICATION_TASK_INCOMPLETE;
                $systemEvent = Events::SYSTEM_TASK_INCOMPLETE;

                if ($task->isComplete()) {
                    $notificationEvent = Events::NOTIFICATION_TASK_COMPLETE;
                    $systemEvent = Events::SYSTEM_TASK_COMPLETE;
                }
                break;
            case TaskStatusConstant::NOTIFY:
                $notificationEvent = Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE;
                $systemEvent = Events::SYSTEM_TASK_NOTIFY_INCOMPLETE;
                break;
            default:
                throw new RuntimeException(sprintf('Type event "%s" for onTaskChangeStatus not exist', $type));
        }

        $this->eventDispatcher->dispatch($genericEvent, $notificationEvent);
        $this->eventDispatcher->dispatch($genericEvent, $systemEvent);
    }

    public function onTaskReminderCreate(Task $task): void
    {
        $genericEvent = new TaskGenericEvent;
        $genericEvent->task = $task;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_REMIND_DEADLINE_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_REMIND_CREATE);
    }
}
