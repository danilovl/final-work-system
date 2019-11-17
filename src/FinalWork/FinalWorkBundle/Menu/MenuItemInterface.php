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

interface MenuItemInterface
{
    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * @return MenuItemInterface
     */
    public function setName(string $name): self;

    /**
     * @return string|null
     */
    public function getUri(): ?string;

    /**
     * @param string|null $uri
     * @return MenuItemInterface
     */
    public function setUri(?string $uri): self;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     * @return MenuItemInterface
     */
    public function setLabel(string $label): self;

    /**
     * @return boolean
     */
    public function isDisplayed(): bool;

    /**
     * @param bool $bool
     * @return MenuItemInterface
     */
    public function setIsDisplayed(bool $bool): self;

    /**
     * @param MenuItemInterface $child
     * @param array $options
     * @return MenuItemInterface
     */
    public function addChild(MenuItemInterface $child, array $options = []): self;

    /**
     * @param string $name
     * @return MenuItemInterface|null
     */
    public function getChild(string $name): ?self;

    /**
     * @return MenuItemInterface|null
     */
    public function getParent(): ?self;

    /**
     * @param MenuItemInterface|null $parent
     * @return MenuItemInterface
     */
    public function setParent(?MenuItemInterface $parent): self;

    /**
     * @return MenuItemInterface[]
     */
    public function getChildren(): array;

    /**
     * @param array $children
     * @return MenuItemInterface
     */
    public function setChildren(array $children): self;

    /**
     * @param string $name
     * @return MenuItemInterface
     */
    public function removeChild(string $name): self;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param array|null $attributes
     * @return MenuItemInterface
     */
    public function setAttributes(?array $attributes): self;

    /**
     * @param string $key
     * @param mixed $value
     * @return MenuItemInterface
     */
    public function addAttribute(string $key, $value): self;

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute(string $key);
}