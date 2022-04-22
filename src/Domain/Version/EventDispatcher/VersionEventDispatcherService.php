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

namespace App\Domain\Version\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\EventDispatcher\GenericEvent\VersionGenericEvent;
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VersionEventDispatcherService
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private AsyncService $asyncService
    ) {
    }

    public function onVersionCreate(Media $media): void
    {
        $genericEvent = new VersionGenericEvent;
        $genericEvent->media = $media;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_VERSION_CREATE);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_VERSION_CREATE);
        });
    }

    public function onVersionEdit(Media $media): void
    {
        $genericEvent = new VersionGenericEvent;
        $genericEvent->media = $media;

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_VERSION_EDIT);
            $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_VERSION_EDIT);
        });
    }
}
