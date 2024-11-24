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

namespace Domain\Widget\Twig\Runtime\Widget;

use App\Domain\Widget\Twig\Runtime\HomepageNotifyWidgetRuntime;
use App\Domain\Widget\WidgetItem\BaseWidget;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HomepageNotifyWidgetRuntimeTest extends TestCase
{
    public function testRenderNotify(): void
    {
        $widget = new class extends BaseWidget {
            public function render(): ?string
            {
                return 'text widget text';
            }
        };

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(1))
            ->method('get')
            ->willReturn($widget);

        $parameterServiceInterface = $this->createMock(ParameterServiceInterface::class);
        $parameterServiceInterface->expects($this->exactly(1))
            ->method('getArray')
            ->willReturn(['homepage_notify']);

        $homepageNotifyWidgetRuntime = new HomepageNotifyWidgetRuntime($container, $parameterServiceInterface);

        $this->assertEquals('text widget text', $homepageNotifyWidgetRuntime->renderNotify());
    }

    public function testRenderNotifyNothing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $parameterServiceInterface = $this->createMock(ParameterServiceInterface::class);
        $parameterServiceInterface->expects($this->once())
            ->method('getArray')
            ->willReturn(['homepage_notify']);

        $homepageNotifyWidgetRuntime = new HomepageNotifyWidgetRuntime($container, $parameterServiceInterface);


        $this->assertNull($homepageNotifyWidgetRuntime->renderNotify());
    }
}
