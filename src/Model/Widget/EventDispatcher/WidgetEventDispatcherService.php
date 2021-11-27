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

namespace App\Model\Widget\EventDispatcher;

use App\EventSubscriber\Events;
use App\Model\Widget\EventDispatcher\GenericEvent\WidgetGenericGenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WidgetEventDispatcherService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onGroupReplace(WidgetGenericGenericEvent $widgetGenericGenericEvent): void
    {
        $this->eventDispatcher->dispatch($widgetGenericGenericEvent, Events::WIDGET_GROUP_REPLACE);
    }
}
