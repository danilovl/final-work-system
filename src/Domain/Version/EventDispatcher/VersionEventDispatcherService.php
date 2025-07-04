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
use App\Infrastructure\Service\EventDispatcherService;

readonly class VersionEventDispatcherService
{
    public function __construct(
        private EventDispatcherService $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onVersionCreate(Media $media): void
    {
        $genericEvent = new VersionGenericEvent($media);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::WORK_VERSION_CREATE);
        });
    }

    public function onVersionEdit(Media $media): void
    {
        $genericEvent = new VersionGenericEvent($media);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::WORK_VERSION_EDIT);
        });
    }
}
