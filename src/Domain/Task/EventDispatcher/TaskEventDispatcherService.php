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

use App\Application\EventSubscriber\Events;
use App\Application\Exception\RuntimeException;
use App\Domain\Task\Constant\TaskStatusConstant;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\EventDispatcher\GenericEvent\TaskGenericEvent;
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaskEventDispatcherService
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onTaskCreate(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        if (!$task->isActive()) {
            return;
        }

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_CREATE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_CREATE);
        });
    }

    public function onTaskEdit(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        if (!$task->isActive()) {
            return;
        }

        $this->asyncService->add(function () use ($genericEvent, $task): void {
            $this->eventDispatcher->dispatch(
                $genericEvent,
                $task->getSystemEvents()->isEmpty() ? Events::NOTIFICATION_TASK_CREATE : Events::NOTIFICATION_TASK_EDIT
            );

            $this->eventDispatcher->dispatch(
                $genericEvent,
                $task->getSystemEvents()->isEmpty() ? Events::SYSTEM_TASK_CREATE : Events::SYSTEM_TASK_EDIT
            );
        });
    }

    public function onTaskNotifyComplete(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_NOTIFY_COMPLETE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_NOTIFY_COMPLETE);
        });
    }

    public function onTaskChangeStatus(Task $task, string $type): void
    {
        $genericEvent = new TaskGenericEvent($task, $type);

        switch ($type) {
            case TaskStatusConstant::ACTIVE->value:
                $notificationEvent = Events::NOTIFICATION_TASK_CREATE;
                $systemEvent = Events::SYSTEM_TASK_CREATE;
                break;
            case TaskStatusConstant::COMPLETE->value:
                $notificationEvent = Events::NOTIFICATION_TASK_INCOMPLETE;
                $systemEvent = Events::SYSTEM_TASK_INCOMPLETE;

                if ($task->isComplete()) {
                    $notificationEvent = Events::NOTIFICATION_TASK_COMPLETE;
                    $systemEvent = Events::SYSTEM_TASK_COMPLETE;
                }
                break;
            case TaskStatusConstant::NOTIFY->value:
                $notificationEvent = Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE;
                $systemEvent = Events::SYSTEM_TASK_NOTIFY_INCOMPLETE;
                break;
            default:
                throw new RuntimeException(sprintf('Type event "%s" for onTaskChangeStatus not exist', $type));
        }

        $this->asyncService->add(function () use ($genericEvent, $notificationEvent, $systemEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, $notificationEvent);
            $this->eventDispatcher->dispatch($genericEvent, $systemEvent);
        });
    }

    public function onTaskReminderCreate(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_TASK_REMIND_DEADLINE_CREATE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_TASK_REMIND_CREATE);
        });
    }
}
