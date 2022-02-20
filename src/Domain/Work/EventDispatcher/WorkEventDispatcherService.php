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

namespace App\Domain\Work\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\User\EventDispatcher\GenericEvent\UserGenericEvent;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\GenericEvent\WorkGenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onWorkCreate(Work $work): void
    {
        $genericEvent = new WorkGenericEvent;
        $genericEvent->work = $work;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_WORK_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_WORK_CREATE);
    }

    public function onWorkEdit(Work $work): void
    {
        $genericEvent = new WorkGenericEvent;
        $genericEvent->work = $work;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_WORK_EDIT);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_WORK_EDIT);
    }

    public function onWorkEditAuthor(Work $work): void
    {
        $genericEvent = new UserGenericEvent;
        $genericEvent->user = $work->getAuthor();

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_USER_EDIT);

        $genericEvent = new WorkGenericEvent;
        $genericEvent->work = $work;
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_WORK_AUTHOR_EDIT);
    }
}