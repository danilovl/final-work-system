<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\Widget\Interfaces;

interface WidgetManagerInterface
{
    public function addWidget(string $name, string $serviceName): void;

    public function hasWidget(string $name): bool;

    public function getWidget(string $name): WidgetInterface;

    /**
     * @param string[] $widgets
     */
    public function addWidgetGroup(string $name, array $widgets): void;

    /**
     * @param string[] $widgets
     */
    public function replaceWidgetGroup(string $name, array $widgets): void;

    public function hasWidgetGroup(string $name): bool;

    public function getWidgetGroup(string $name): array;
}
