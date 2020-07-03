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

namespace App\Services\Interfaces;

interface SeoPageInterface
{
    public function setTitle(?string $title): SeoPageInterface;
    public function addTitle(string $title, string $separator = null): SeoPageInterface;
    public function getTitle(): ?string;
    public function getTransTitle(string $title = null): ?string;
    public function addMeta(string $type, string $name, string $value, array $extras = []): self;
    public function hasMeta(string $type, string $name): bool;
    public function getMetas(): array;
    public function setMetas(array $metas): self;
}
