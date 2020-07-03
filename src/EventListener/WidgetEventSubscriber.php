<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\EventListener;

use App\EventDispatcher\GenericEvent\WidgetGenericGenericEvent;
use App\Services\WidgetManagerService;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class WidgetEventSubscriber implements EventSubscriberInterface
{
    private WidgetManagerService $widgetManagerService;

    public function __construct(WidgetManagerService $widgetManagerService)
    {
        $this->widgetManagerService = $widgetManagerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::WIDGET_GROUP_REPLACE => 'onGroupReorder',
        ];
    }

    public function onGroupReorder(GenericEvent $event): void
    {
        /** @var WidgetGenericGenericEvent $widgetGenericEventSubject */
        $widgetGenericEventSubject = $event->getSubject();

        $this->widgetManagerService->replaceWidgetGroup(
            $widgetGenericEventSubject->groupName,
            $widgetGenericEventSubject->groupWidgets
        );
    }
}
