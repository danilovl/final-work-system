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

namespace App\Infrastructure\Event\EventDispatcher;

use Danilovl\AsyncBundle\Service\AsyncService;
use App\Infrastructure\Event\EventDispatcher\GenericEvent\{EntityCreateEvent, EntityRemoveEvent, EntitySaveEvent};
use App\Infrastructure\Service\EventDispatcherService;
use App\Infrastructure\Event\InfrastructureEvents;

readonly class EntityEventDispatcher
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onCreate(object $entity): void
    {
        $event = new EntityCreateEvent($entity);
        $this->eventDispatcher->dispatch($event, InfrastructureEvents::ENTITY_CREATE);

        $this->asyncService->add(function () use ($event): void {
            $this->eventDispatcher->dispatch($event, InfrastructureEvents::ENTITY_CREATE_ASYNC);
        });
    }

    public function onRemove(): void
    {
        $this->eventDispatcher->dispatch(new EntityRemoveEvent, InfrastructureEvents::ENTITY_REMOVE);
    }

    public function onSave(): void
    {
        $this->eventDispatcher->dispatch(new EntitySaveEvent, InfrastructureEvents::ENTITY_SAVE);
    }
}
