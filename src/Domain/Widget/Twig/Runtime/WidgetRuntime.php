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

namespace App\Domain\Widget\Twig\Runtime;

use App\Domain\Widget\Service\WidgetManagerService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;

class WidgetRuntime
{
    public function __construct(private readonly WidgetManagerService $widgetManager) {}

    #[AsTwigFunction('widget')]
    public function widget(
        string $name,
        array $parameters = []
    ): ?string {
        $widget = $this->widgetManager->getWidget($name);
        $widget->setParameters($parameters);

        return $widget->render();
    }

    #[AsTwigFunction('widget_group')]
    public function widgetGroup(
        string $name,
        array $parameters = []
    ): ?string {
        $widgets = $this->widgetManager->getWidgetGroup($name);

        $content = null;
        foreach ($widgets as $widget) {
            $widget->setParameters($parameters[$widget->getName()] ?? []);
            $content .= $widget->render();
        }

        return $content;
    }
}
