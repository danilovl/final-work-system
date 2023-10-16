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

namespace App\Tests\Unit\Application\Twig;

use App\Application\Twig\WidgetExtension;
use App\Application\Widget\BaseWidget;
use App\Domain\Widget\Service\WidgetManagerService;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class WidgetExtensionTest extends TestCase
{
    private readonly WidgetManagerService $widgetManagerService;
    private readonly WidgetExtension $widgetExtension;

    protected function setUp(): void
    {
        $this->widgetManagerService = $this->createMock(WidgetManagerService::class);

        $this->widgetExtension = new WidgetExtension($this->widgetManagerService);
    }

    public function testGetFunctions(): void
    {
        $twigFunction = array_map(static function (TwigFunction $twigFunction): string {
            return $twigFunction->getName();
        }, $this->widgetExtension->getFunctions());

        $this->assertEquals(
            ['widget', 'widget_group', 'widget_homepage_notify'],
            $twigFunction
        );
    }

    public function testWidget(): void
    {
        $widget = new class extends BaseWidget {
            public function render(): ?string
            {
                return 'text widget text';
            }
        };

        $this->widgetManagerService
            ->expects($this->once())
            ->method('getWidget')
            ->willReturn($widget);

        $result = $this->widgetExtension->widget('widget');

        $this->assertSame('text widget text', $result);
    }

    public function testWidgetGroup(): void
    {
        $widgetGroup = [
            new class extends BaseWidget {
                public function render(): ?string
                {
                    return 'text widget one';
                }
            },
            new class extends BaseWidget {
                public function render(): ?string
                {
                    return 'text widget two';
                }
            }
        ];

        $this->widgetManagerService
            ->expects($this->once())
            ->method('getWidgetGroup')
            ->willReturn($widgetGroup);

        $result = $this->widgetExtension->widgetGroup('widget');

        $this->assertSame('text widget onetext widget two', $result);
    }
}
