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

namespace App\Tests\Unit\Application\DependencyInjection\Compiler;

use App\Application\DependencyInjection\Compiler\WidgetCompilerPass;
use App\Application\Widget\BaseWidget;
use App\Domain\Widget\Service\WidgetManagerService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\{
    Definition,
    ContainerBuilder
};

class WidgetCompilerPassTest extends TestCase
{
    public function testWidgetCompilerPass(): void
    {
        $containerBuilder = new ContainerBuilder;

        $container = $this->createMock(ContainerInterface::class);
        $widgetManagerDefinition = new Definition(WidgetManagerService::class);
        $widgetManagerDefinition->setArguments([$container]);

        $containerBuilder->setDefinition(WidgetManagerService::class, $widgetManagerDefinition);

        $taggedService1 = new Definition(BaseWidget::class);
        $taggedService1->addTag('app.widgets', ['alias' => 'widget1']);
        $containerBuilder->setDefinition('your_widget1_service', $taggedService1);

        $taggedService2 = new Definition(BaseWidget::class);
        $taggedService2->addTag('app.widgets', ['alias' => 'widget2']);
        $containerBuilder->setDefinition('your_widget2_service', $taggedService2);

        $containerBuilder->setParameter('widget_group', []);

        $compilerPass = new WidgetCompilerPass;
        $compilerPass->process($containerBuilder);

        /** @var WidgetManagerService $widgetManagerService */
        $widgetManagerService = $containerBuilder->get(WidgetManagerService::class);

        $this->assertTrue($widgetManagerService->hasWidget('widget1'));
        $this->assertTrue($widgetManagerService->hasWidget('widget2'));

        $this->assertFalse($widgetManagerService->hasWidget('widget3'));
        $this->assertFalse($widgetManagerService->hasWidget('widget4'));
    }
}
