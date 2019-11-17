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

namespace FinalWork\FinalWorkBundle\Menu;

use InvalidArgumentException;

class MenuItem implements MenuItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var boolean
     */
    private $isDisplayed = true;

    /**
     * @var MenuItemInterface[]
     */
    private $children = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var MenuItemInterface|null
     */
    private $parent;

    /**
     * MenuItem constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MenuItemInterface
     */
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

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     * @return MenuItem
     */
    public function setUri(?string $uri): MenuItemInterface
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }

    /**
     * @param string $label
     * @return MenuItemInterface
     */
    public function setLabel(string $label): MenuItemInterface
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->isDisplayed;
    }

    /**
     * @param bool $bool
     * @return MenuItemInterface
     */
    public function setIsDisplayed(bool $bool): MenuItemInterface
    {
        $this->isDisplayed = $bool;

        return $this;
    }

    /**
     * @param MenuItemInterface $child
     * @param array $options
     * @return MenuItemInterface
     */
    public function addChild(MenuItemInterface $child, array $options = []): MenuItemInterface
    {
        $child->setParent($this);
        $this->children[$child->getName()] = $child;

        return $child;
    }

    /**
     * @param string $name
     * @return MenuItemInterface|null
     */
    public function getChild(string $name): ?MenuItemInterface
    {
        return $this->children[$name] ?? null;
    }

    /**
     * @return MenuItemInterface|null
     */
    public function getParent(): ?MenuItemInterface
    {
        return $this->parent;
    }

    /**
     * @param MenuItemInterface|null $parent
     * @return MenuItemInterface
     */
    public function setParent(?MenuItemInterface $parent): MenuItemInterface
    {
        if ($parent === $this) {
            throw new InvalidArgumentException('Item cannot be a child of itself');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @return MenuItemInterface[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     * @return MenuItemInterface
     */
    public function setChildren(array $children): MenuItemInterface
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param MenuItemInterface $children
     * @return MenuItemInterface
     */
    public function addChildren(MenuItemInterface $children): MenuItemInterface
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * @param string $name
     * @return MenuItemInterface
     */
    public function removeChild(string $name): MenuItemInterface
    {
        if (isset($this->children[$name])) {
            $this->children[$name]->setParent(null);
            unset($this->children[$name]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return MenuItemInterface
     */
    public function setAttributes(?array $attributes): MenuItemInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return MenuItemInterface
     */
    public function addAttribute(string $key, $value): MenuItemInterface
    {
        if (!isset($this->attributes[$key])) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
}
