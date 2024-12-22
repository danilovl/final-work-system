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

namespace Domain\Widget\Twig\Runtime;

use App\Domain\Widget\Service\WidgetManagerService;
use App\Domain\Widget\Twig\Runtime\WidgetRuntime;
use App\Domain\Widget\WidgetItem\BaseWidget;
use PHPUnit\Framework\TestCase;

class WidgetRuntimeTest extends TestCase
{
    private WidgetManagerService $widgetManagerService;
    private WidgetRuntime $widgetRuntime;

    protected function setUp(): void
    {
        $this->widgetManagerService = $this->createMock(WidgetManagerService::class);

        $this->widgetRuntime = new WidgetRuntime($this->widgetManagerService);
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

        $result = $this->widgetRuntime->widget('widget');

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

        $result = $this->widgetRuntime->widgetGroup('widget');

        $this->assertSame('text widget onetext widget two', $result);
    }
}
