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

use FinalWork\FinalWorkBundle\Entity\Work;
use FinalWork\FinalWorkBundle\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class WorkEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * WorkEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Work $work
     */
    public function onWorkCreate(Work $work): void
    {
        $genericEvent = new GenericEvent($work);
        $this->eventDispatcher->dispatch(Events::NOTIFICATION_WORK_CREATE, $genericEvent);
        $this->eventDispatcher->dispatch(Events::SYSTEM_WORK_CREATE, $genericEvent);
    }

    /**
     * @param Work $work
     */
    public function onWorkEdit(Work $work): void
    {
        $genericEvent = new GenericEvent($work);
        $this->eventDispatcher->dispatch(Events::NOTIFICATION_WORK_EDIT, $genericEvent);
        $this->eventDispatcher->dispatch(Events::SYSTEM_WORK_EDIT, $genericEvent);
    }

    /**
     * @param Work $work
     */
    public function onWorkEditAuthor(Work $work): void
    {
        $event = new GenericEvent($work->getAuthor());
        $this->eventDispatcher->dispatch(Events::NOTIFICATION_USER_EDIT, $event);

        $event = new GenericEvent($work);
        $this->eventDispatcher->dispatch(Events::SYSTEM_WORK_AUTHOR_EDIT, $event);
    }
}
