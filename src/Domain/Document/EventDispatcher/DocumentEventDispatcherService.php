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

namespace App\Domain\Document\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\EventDispatcher\GenericEvent\MediaGenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onDocumentCreate(Media $media): void
    {
        $genericEvent = new MediaGenericEvent;
        $genericEvent->media = $media;

        $this->eventDispatcher->dispatch($genericEvent, Events::NOTIFICATION_DOCUMENT_CREATE);
        $this->eventDispatcher->dispatch($genericEvent, Events::SYSTEM_DOCUMENT_CREATE);
    }
}