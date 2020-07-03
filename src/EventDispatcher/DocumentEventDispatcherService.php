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

namespace App\EventDispatcher;

use App\Entity\Media;
use App\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class DocumentEventDispatcherService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onDocumentCreate(Media $media): void
    {
        $genericEvent = new GenericEvent($media);

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_DOCUMENT_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_DOCUMENT_CREATE);
    }
}
