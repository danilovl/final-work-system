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

readonly class TaskEventDispatcher
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
            $this->eventDispatcher->dispatch($genericEvent, Events::TASK_CREATE);
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
                $task->getSystemEvents()->isEmpty() ? Events::TASK_CREATE : Events::TASK_EDIT
            );
        });
    }

    public function onTaskNotifyComplete(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::TASK_NOTIFY_COMPLETE);
        });
    }

    public function onTaskChangeStatus(Task $task, string $type): void
    {
        $genericEvent = new TaskGenericEvent($task, $type);

        switch ($type) {
            case TaskStatusConstant::ACTIVE->value:
                $notificationEvent = Events::TASK_CREATE;

                break;
            case TaskStatusConstant::COMPLETE->value:
                $notificationEvent = Events::TASK_INCOMPLETE;

                if ($task->isComplete()) {
                    $notificationEvent = Events::TASK_COMPLETE;
                }

                break;
            case TaskStatusConstant::NOTIFY->value:
                $notificationEvent = Events::TASK_NOTIFY_INCOMPLETE;

                break;
            default:
                throw new RuntimeException(sprintf('Type event "%s" for onTaskChangeStatus not exist', $type));
        }

        $this->asyncService->add(function () use ($genericEvent, $notificationEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, $notificationEvent);
        });
    }

    public function onTaskReminderCreate(Task $task): void
    {
        $genericEvent = new TaskGenericEvent($task);

        $this->eventDispatcher->dispatch($genericEvent, Events::TASK_REMIND_DEADLINE_CREATE);
    }
}
