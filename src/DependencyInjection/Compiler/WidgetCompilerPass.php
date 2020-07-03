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

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WidgetCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $widgetManager = $containerBuilder->getDefinition('app.widget');
        $taggedServices = $containerBuilder->findTaggedServiceIds('app.widgets');

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    continue;
                }

                $alias = $attributes['alias'];
                $widgetManager->addMethodCall('addWidget', [$alias, $serviceId]);

                $widget = $containerBuilder->getDefinition($serviceId);
                $widget->addMethodCall('setName', [$alias]);
            }
        }

        $widgetGroups = $containerBuilder->getParameter('widget_group');
        foreach ($widgetGroups as $name => $widgets) {
            $widgetManager->addMethodCall('addWidgetGroup', [$name, $widgets]);
        }
    }
}