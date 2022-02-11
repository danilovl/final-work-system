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

namespace App\Application\Widget;

use App\Application\Interfaces\Widget\WidgetInterface;

abstract class BaseWidget implements WidgetInterface
{
    protected string $name = 'base';
    protected array $parameters = [];

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function render(): ?string
    {
        return null;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getRenderParameters(): array
    {
        return [];
    }
}