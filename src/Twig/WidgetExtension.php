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

namespace App\Twig;

use App\Service\WidgetManagerService;
use App\Twig\Runtime\HomepageNotifyWidgetRuntime;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class WidgetExtension extends AbstractExtension
{
    public function __construct(private WidgetManagerService $widgetManager)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('widget', [$this, 'widget'], ['is_safe' => ['html']]),
            new TwigFunction('widget_group', [$this, 'widgetGroup'], ['is_safe' => ['html']]),
            new TwigFunction('widget_homepage_notify', [HomepageNotifyWidgetRuntime::class, 'renderNotify'], ['is_safe' => ['html']])

        ];
    }

    public function widget(
        string $name,
        array $parameters = []
    ): ?string {
        $widget = $this->widgetManager->getWidget($name);
        $widget->setParameters($parameters);

        return $widget->render();
    }

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
