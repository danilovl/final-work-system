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

namespace FinalWork\FinalWorkBundle\Services\Seo;

interface SeoPageInterface
{
    /**
     * @param string|null $title
     * @return SeoPageInterface
     */
    public function setTitle(?string $title): SeoPageInterface;

    /**
     * @param string $title
     * @param string|null $separator
     * @return SeoPageInterface
     */
    public function addTitle(string $title, string $separator = null): SeoPageInterface;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param string|null $title
     * @return string|null
     */
    public function getTransTitle(string $title = null): ?string;

    /**
     * @param string $type
     * @param string $name
     * @param string $value
     * @param array $extras
     * @return SeoPageInterface
     */
    public function addMeta(string $type, string $name, string $value, array $extras = []): self;

    /**
     * @param string $type
     * @param string $name
     * @return bool
     */
    public function hasMeta(string $type, string $name): bool;

    /**
     * @return array
     */
    public function getMetas(): array;

    /**
     * @param array $metas
     * @return SeoPageInterface
     */
    public function setMetas(array $metas): self;
}
