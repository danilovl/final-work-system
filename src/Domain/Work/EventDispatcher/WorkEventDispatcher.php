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
use Danilovl\AsyncBundle\Service\AsyncService;
use App\Infrastructure\Service\EventDispatcherService;

readonly class WorkEventDispatcher
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onWorkCreate(Work $work): void
    {
        $genericEvent = new WorkGenericEvent($work);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::WORK_CREATE);
        });
    }

    public function onWorkEdit(Work $work): void
    {
        $genericEvent = new WorkGenericEvent($work);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::WORK_EDIT);
        });
    }

    public function onWorkEditAuthor(Work $work): void
    {
        $genericEventUser = new UserGenericEvent($work->getAuthor(), $work->getSupervisor());
        $genericEventWork = new WorkGenericEvent($work);

        $this->asyncService->add(function () use ($genericEventUser, $genericEventWork): void {
            $this->eventDispatcher->dispatch($genericEventUser, Events::USER_EDIT);
            $this->eventDispatcher->dispatch($genericEventWork, Events::WORK_AUTHOR_EDIT);
        });
    }

    public function onWorkReminderDeadlineCreate(Work $work): void
    {
        $genericEvent = new WorkGenericEvent($work);

        $this->eventDispatcher->dispatch($genericEvent, Events::WORK_REMIND_DEADLINE_CREATE);
    }
}
