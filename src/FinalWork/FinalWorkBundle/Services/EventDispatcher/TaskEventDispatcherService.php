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

namespace FinalWork\FinalWorkBundle\Services\EventDispatcher;

use FinalWork\FinalWorkBundle\Constant\TaskStatusConstant;
use FinalWork\FinalWorkBundle\Entity\Task;
use FinalWork\FinalWorkBundle\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class TaskEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * TaskEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Task $task
     */
    public function onTaskCreate(Task $task): void
    {
        $event = new GenericEvent($task);

        if ($task->isActive()) {
            $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_CREATE, $event);
            $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_CREATE, $event);
        }
    }

    /**
     * @param Task $task
     */
    public function onTaskEdit(Task $task): void
    {
        $event = new GenericEvent($task);

        if ($task->isActive()) {
            if ($task->getSystemEvents()) {
                $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_EDIT, $event);
                $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_EDIT, $event);
            } else {
                $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_CREATE, $event);
                $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_CREATE, $event);
            }
        }
    }

    /**
     * @param Task $task
     */
    public function onTaskNotifyComplete(Task $task): void
    {
        $event = new GenericEvent($task);

         $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_NOTIFY_COMPLETE, $event);
         $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_NOTIFY_COMPLETE, $event);
    }

    /**
     * @param Task $task
     * @param string $type
     */
    public function onTaskChangeStatus(Task $task, string $type): void
    {
        switch ($type) {
            case TaskStatusConstant::ACTIVE:
                $event = new GenericEvent($task);
                $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_CREATE, $event);
                $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_CREATE, $event);
                break;
            case TaskStatusConstant::COMPLETE:
                $event = new GenericEvent($task);
                if ($task->isComplete()) {
                    $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_COMPLETE, $event);
                    $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_COMPLETE, $event);
                } else {
                    $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_INCOMPLETE, $event);
                    $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_INCOMPLETE, $event);
                }
                break;
            case TaskStatusConstant::NOTIFY:
                $event = new GenericEvent($task);
                $this->eventDispatcher->dispatch(Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE, $event);
                $this->eventDispatcher->dispatch(Events::SYSTEM_TASK_NOTIFY_INCOMPLETE, $event);
                break;
        }
    }
}
