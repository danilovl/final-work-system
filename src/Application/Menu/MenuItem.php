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

namespace App\Application\Menu;

use App\Application\Interfaces\Menu\MenuItemInterface;
use InvalidArgumentException;

class MenuItem implements MenuItemInterface
{
    private ?string $label = null;
    private ?string $uri = null;
    private bool $isDisplayed = true;
    private array $children = [];
    private ?array $attributes = [];
    private ?MenuItemInterface $parent = null;

    public function __construct(private string $name) {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): MenuItemInterface
    {
        $oldName = $this->name;
        if ($oldName == $name) {
            return $this;
        }

        $parent = $this->getParent();
        if ($parent !== null && isset($parent[$name])) {
            throw new InvalidArgumentException('Cannot rename item, name is already used by sibling.');
        }

        $this->name = $name;
        if ($parent !== null) {
            $names = array_keys($parent->getChildren());
            $items = array_values($parent->getChildren());

            $offset = array_search($oldName, $names, true);
            $names[$offset] = $name;

            $parent->setChildren(array_combine($names, $items));
        }

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): MenuItemInterface
    {
        $this->uri = $uri;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }

    public function setLabel(string $label): MenuItemInterface
    {
        $this->label = $label;

        return $this;
    }

    public function isDisplayed(): bool
    {
        return $this->isDisplayed;
    }

    public function setIsDisplayed(bool $bool): MenuItemInterface
    {
        $this->isDisplayed = $bool;

        return $this;
    }

    public function addChild(MenuItemInterface $child, array $options = []): MenuItemInterface
    {
        $child->setParent($this);
        $this->children[$child->getName()] = $child;

        return $child;
    }

    public function getChild(string $name): ?MenuItemInterface
    {
        return $this->children[$name] ?? null;
    }

    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    public function setParent(?MenuItemInterface $parent): MenuItemInterface
    {
        if ($parent === $this) {
            throw new InvalidArgumentException('Item cannot be a child of itself');
        }

        $this->parent = $parent;

        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): MenuItemInterface
    {
        $this->children = $children;

        return $this;
    }

    public function addChildren(MenuItemInterface $children): MenuItemInterface
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChild(string $name): MenuItemInterface
    {
        if (isset($this->children[$name])) {
            $this->children[$name]->setParent(null);
            unset($this->children[$name]);
        }

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): MenuItemInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addAttribute(string $key, mixed $value): MenuItemInterface
    {
        if (!isset($this->attributes[$key])) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
}
