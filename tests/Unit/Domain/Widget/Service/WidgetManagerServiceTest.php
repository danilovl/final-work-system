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

namespace App\Tests\Unit\Domain\Widget\Service;

use App\Application\Exception\InvalidArgumentException;
use App\Domain\Widget\Interfaces\WidgetInterface;
use App\Domain\Widget\Service\WidgetManagerService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class WidgetManagerServiceTest extends TestCase
{
    private ContainerInterface $container;

    private WidgetManagerService $widgetManagerService;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->widgetManagerService = new WidgetManagerService($this->container);
    }

    public function testAddWidget(): void
    {
        $this->widgetManagerService->addWidget('test_widget', 'test_service');

        $this->assertTrue($this->widgetManagerService->hasWidget('test_widget'));

        $this->expectException(InvalidArgumentException::class);
        $this->widgetManagerService->addWidget('test_widget', 'test_service');
    }

    public function testAddWidgetGroup(): void
    {
        $this->widgetManagerService->addWidget('test_widget_1', 'test_service_1');
        $this->widgetManagerService->addWidget('test_widget_2', 'test_service_2');

        $this->widgetManagerService->addWidgetGroup('test_widget_group', ['test_widget_1', 'test_widget_2']);

        $this->assertTrue($this->widgetManagerService->hasWidgetGroup('test_widget_group'));

        $this->expectException(InvalidArgumentException::class);
        $this->widgetManagerService->addWidgetGroup('test_widget_group', ['test_widget_1', 'test_widget_2']);
    }

    public function testGetWidget(): void
    {
        $widget = $this->createMock(WidgetInterface::class);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('test_service')
            ->willReturn($widget);

        $this->widgetManagerService->addWidget('test_widget', 'test_service');

        $result = $this->widgetManagerService->getWidget('test_widget');

        $this->assertSame($widget, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->widgetManagerService->getWidget('invalid_widget');
    }

    public function testGetWidgetGroup(): void
    {
        $widget1 = $this->createMock(WidgetInterface::class);
        $widget2 = $this->createMock(WidgetInterface::class);

        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(['test_service_1'], ['test_service_2'])
            ->willReturnOnConsecutiveCalls($widget1, $widget2);

        $this->widgetManagerService->addWidget('test_widget_1', 'test_service_1');
        $this->widgetManagerService->addWidget('test_widget_2', 'test_service_2');
        $this->widgetManagerService->addWidgetGroup('test_widget_group', ['test_widget_1', 'test_widget_2']);

        $result = $this->widgetManagerService->getWidgetGroup('test_widget_group');

        $this->assertSame([$widget1, $widget2], $result);

        $this->expectException(InvalidArgumentException::class);
        $this->widgetManagerService->getWidgetGroup('invalid_widget_group');
    }

    public function testReplaceWidgetGroup(): void
    {
        $widget2 = $this->createMock(WidgetInterface::class);

        $this->container
            ->expects($this->exactly(1))
            ->method('get')
            ->willReturn(['test_service_2'])
            ->willReturnOnConsecutiveCalls($widget2);

        $this->widgetManagerService->addWidget('test_widget_1', 'test_service_1');
        $this->widgetManagerService->addWidget('test_widget_2', 'test_service_2');
        $this->widgetManagerService->addWidgetGroup('test_widget_group', ['test_widget_1', 'test_widget_2']);

        $this->widgetManagerService->replaceWidgetGroup('test_widget_group', ['test_widget_2']);

        $this->assertSame([$widget2], $this->widgetManagerService->getWidgetGroup('test_widget_group'));

        $this->expectException(InvalidArgumentException::class);
        $this->widgetManagerService->replaceWidgetGroup('invalid_widget_group', ['test_widget_2']);
    }
}
