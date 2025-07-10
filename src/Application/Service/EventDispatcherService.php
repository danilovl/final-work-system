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

namespace App\Application\Service;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class EventDispatcherService implements EventDispatcherInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher) {}

    public function dispatch(object $event, ?string $eventName = null): object
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
