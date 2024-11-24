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

interface WidgetInterface
{
    public function getName(): ?string;

    public function setName(string $name): void;

    public function render(): ?string;

    public function getRenderParameters(): array;

    public function setParameters(array $parameters): void;
}