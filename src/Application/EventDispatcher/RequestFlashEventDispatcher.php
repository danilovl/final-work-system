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

use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\EventDispatcherService;
use stdClass;

readonly class RequestFlashEventDispatcher
{
    public function __construct(private EventDispatcherService $eventDispatcher) {}

    public function onCreateFailure(): void
    {
        $this->eventDispatcher->dispatch(new stdClass, Events::REQUEST_FLASH_CREATE_FAILURE);
    }

    public function onSaveFailure(): void
    {
        $this->eventDispatcher->dispatch(new stdClass, Events::REQUEST_FLASH_SAVE_FAILURE);
    }

    public function onRemoveFailure(): void
    {
        $this->eventDispatcher->dispatch(new stdClass, Events::REQUEST_FLASH_DELETE_FAILURE);
    }
}
