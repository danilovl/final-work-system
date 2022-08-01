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

namespace App\Application\EventSubscriber;

use App\Domain\Widget\EventDispatcher\GenericEvent\WidgetGenericGenericEvent;
use App\Domain\Widget\Service\WidgetManagerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WidgetEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly WidgetManagerService $widgetManagerService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::WIDGET_GROUP_REPLACE => 'onGroupReorder'
        ];
    }

    public function onGroupReorder(WidgetGenericGenericEvent $event): void
    {
        $this->widgetManagerService->replaceWidgetGroup(
            $event->groupName,
            $event->groupWidgets
        );
    }
}
