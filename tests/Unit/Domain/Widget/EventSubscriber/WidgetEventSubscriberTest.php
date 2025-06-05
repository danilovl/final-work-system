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

namespace App\Tests\Unit\Domain\Widget\EventSubscriber;

use App\Application\EventSubscriber\Events;
use App\Domain\Widget\EventDispatcher\GenericEvent\WidgetGenericGenericEvent;
use App\Domain\Widget\EventSubscriber\WidgetEventSubscriber;
use App\Domain\Widget\Service\WidgetManagerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WidgetEventSubscriberTest extends TestCase
{
    private MockObject&WidgetManagerService $widgetManagerService;

    private WidgetEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->widgetManagerService = $this->createMock(WidgetManagerService::class);

        $this->subscriber = new WidgetEventSubscriber($this->widgetManagerService);
    }

    public function testOnGroupReorder(): void
    {
        $event = new WidgetGenericGenericEvent;
        $event->groupName = 'group';
        $event->groupWidgets = [];

        $this->widgetManagerService
            ->expects($this->once())
            ->method('replaceWidgetGroup');

        $this->subscriber->onGroupReorder($event);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subscriber::getSubscribedEvents();

        $this->assertEquals('onGroupReorder', $subscribedEvents[Events::WIDGET_GROUP_REPLACE]);
    }
}
