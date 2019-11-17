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

use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\EventListener\Events;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventDispatcherInterface
};

class DocumentEventDispatcherService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * DocumentEventDispatcherService constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Media $media
     */
    public function onDocumentCreate(Media $media): void
    {
        $event = new GenericEvent($media);

        $this->eventDispatcher->dispatch(Events::NOTIFICATION_DOCUMENT_CREATE, $event);
        $this->eventDispatcher->dispatch(Events::SYSTEM_DOCUMENT_CREATE, $event);
    }
}
