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

namespace App\Application\DependencyInjection\Compiler;

use App\Domain\Widget\Service\WidgetManagerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WidgetCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $widgetManager = $container->getDefinition(WidgetManagerService::class);
        $taggedServices = $container->findTaggedServiceIds('app.widgets');

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    continue;
                }

                $alias = $attributes['alias'];
                $widgetManager->addMethodCall('addWidget', [$alias, $serviceId]);

                $widget = $container->getDefinition($serviceId);
                $widget->addMethodCall('setName', [$alias]);
            }
        }

        $widgetGroups = $container->getParameter('widget_group');
        foreach ($widgetGroups as $name => $widgets) {
            $widgetManager->addMethodCall('addWidgetGroup', [$name, $widgets]);
        }
    }
}