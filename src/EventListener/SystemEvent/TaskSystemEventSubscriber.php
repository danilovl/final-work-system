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
    Work,
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
            Events::SYSTEM_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete'
        ];
    }

    public function onTaskCreate(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_CREATE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskEdit(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_EDIT)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_COMPLETE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_INCOMPLETE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskNotifyComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($work->getAuthor());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_NOTIFY_COMPLETE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($task->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }

    public function onTaskNotifyInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent;
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE)
        );

        $recipient = new SystemEventRecipient;
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persistAndFlush($systemEvent);
    }
}
