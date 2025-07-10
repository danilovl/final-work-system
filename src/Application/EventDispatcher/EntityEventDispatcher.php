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

use App\Application\EventDispatcher\GenericEvent\EntityPostFlushGenericEvent;
use App\Application\EventSubscriber\Events;
use Danilovl\AsyncBundle\Service\AsyncService;
use App\Application\Service\EventDispatcherService;

readonly class EntityEventDispatcher
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onPostPersistFlush(object $object): void
    {
        $genericEvent = new EntityPostFlushGenericEvent($object);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::ENTITY_POST_PERSIST_FLUSH);
        });
    }
}
