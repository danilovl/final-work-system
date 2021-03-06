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

namespace App\Service;

use App\Exception\InvalidArgumentException;
use App\Interfaces\{
    WidgetInterface,
    WidgetManagerInterface
};
use Psr\Container\ContainerInterface;

class WidgetManagerService implements WidgetManagerInterface
{
    private array $widgets = [];
    private array $widgetsGroup = [];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function addWidget(string $name, string $serviceName): void
    {
        if (isset($this->widgets[$name])) {
            throw new InvalidArgumentException(sprintf('Widget "%s" is already registered', $name));
        }

        $this->widgets[$name] = $serviceName;
    }

    public function addWidgetGroup(string $name, array $widgets): void
    {
        if (isset($this->widgetsGroup[$name])) {
            throw new InvalidArgumentException(sprintf('Widget group "%s" is already registered', $name));
        }

        foreach ($widgets as $widget) {
            if (!isset($this->widgets[$widget])) {
                throw new InvalidArgumentException(sprintf('Widget "%s" not registered', $name));
            }
        }

        $this->widgetsGroup[$name] = $widgets;
    }

    public function hasWidget(string $name): bool
    {
        return isset($this->widgets[$name]);
    }

    public function hasWidgetGroup(string $name): bool
    {
        return isset($this->widgetsGroup[$name]);
    }

    public function getWidget(string $name): WidgetInterface
    {
        $widget = $this->widgets[$name] ?? null;
        if ($widget !== null) {
            return $this->container->get($widget);
        }

        throw new InvalidArgumentException(sprintf('Widget "%s" not registered', $name));
    }

    /**
     * @return WidgetInterface[]
     */
    public function getWidgetGroup(string $name): array
    {
        $widgetsName = $this->widgetsGroup[$name] ?? null;
        if ($widgetsName !== null) {
            $widgets = [];
            foreach ($widgetsName as $widgetName) {
                $widgets[] = $this->getWidget($widgetName);
            }

            return $widgets;
        }

        throw new InvalidArgumentException(sprintf('Widget group "%s" not registered', $name));
    }

    public function replaceWidgetGroup(string $name, array $widgets): void
    {
        if (!isset($this->widgetsGroup[$name])) {
            throw new InvalidArgumentException(sprintf('Widget group "%s" is not exist', $name));
        }

        foreach ($widgets as $widget) {
            if (!isset($this->widgets[$widget])) {
                throw new InvalidArgumentException(sprintf('Widget "%s" not registered', $name));
            }
        }

        $this->widgetsGroup[$name] = $widgets;
    }
}
