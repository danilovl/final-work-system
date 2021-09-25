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

namespace App\Interfaces;

interface MenuItemInterface
{
    public function getName(): ?string;

    public function setName(string $name): self;

    public function getUri(): ?string;

    public function setUri(?string $uri): self;

    public function getLabel(): string;

    public function setLabel(string $label): self;

    public function isDisplayed(): bool;

    public function setIsDisplayed(bool $bool): self;

    public function addChild(MenuItemInterface $child, array $options = []): self;

    public function getChild(string $name): ?self;

    public function getParent(): ?self;

    public function setParent(?MenuItemInterface $parent): self;

    public function getChildren(): array;

    public function setChildren(array $children): self;

    public function removeChild(string $name): self;

    public function getAttributes(): array;

    public function setAttributes(?array $attributes): self;

    public function addAttribute(string $key, mixed $value): self;

    public function getAttribute(string $key): mixed;
}