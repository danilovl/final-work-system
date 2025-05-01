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
use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class DocumentEventDispatcher
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private AsyncService $asyncService
    ) {}

    public function onDocumentCreate(Media $media): void
    {
        $genericEvent = new MediaGenericEvent($media);

        $this->asyncService->add(function () use ($genericEvent): void {
            $this->eventDispatcher->dispatch($genericEvent, Events::DOCUMENT_CREATE);
        });
    }
}
