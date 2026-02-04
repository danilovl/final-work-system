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

namespace App\Application\EventDispatcher;

use Danilovl\AsyncBundle\Service\AsyncService;
use App\Application\EventDispatcher\GenericEvent\{
    EntityCreateEvent,
    EntityRemoveEvent,
    EntitySaveEvent
};
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;

readonly class EntityEventDispatcher
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onCreate(object $entity): void
    {
        $event = new EntityCreateEvent($entity);
        $this->eventDispatcher->dispatch($event, Events::ENTITY_CREATE);

        $this->asyncService->add(function () use ($event): void {
            $this->eventDispatcher->dispatch($event, Events::ENTITY_CREATE_ASYNC);
        });
    }

    public function onRemove(): void
    {
        $this->eventDispatcher->dispatch(new EntityRemoveEvent, Events::ENTITY_REMOVE);
    }

    public function onSave(): void
    {
        $this->eventDispatcher->dispatch(new EntitySaveEvent, Events::ENTITY_SAVE);
    }
}
