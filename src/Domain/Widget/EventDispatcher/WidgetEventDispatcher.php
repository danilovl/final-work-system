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

namespace App\Domain\Widget\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\Widget\EventDispatcher\GenericEvent\WidgetGenericGenericEvent;
use App\Application\Service\EventDispatcherService;

readonly class WidgetEventDispatcher
{
    public function __construct(private EventDispatcherService $eventDispatcher) {}

    public function onGroupReplace(WidgetGenericGenericEvent $widgetGenericGenericEvent): void
    {
        $this->eventDispatcher->dispatch($widgetGenericGenericEvent, Events::WIDGET_GROUP_REPLACE);
    }
}
