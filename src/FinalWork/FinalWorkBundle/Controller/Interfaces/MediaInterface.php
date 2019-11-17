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

namespace FinalWork\FinalWorkBundle\Controller\Interfaces;

use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Media
};

interface MediaInterface
{
    /**
     * @param Media $media
     * @param int $mediaType
     * @param Work|null $work
     * @return mixed
     */
    public function create(Media $media, int $mediaType, Work $work = null): void;

    /**
     * @param Media $media
     * @param Work|null $work
     * @return mixed
     */
    public function edit(Media $media, Work $work = null): void;

    /**
     * @param Media $media
     * @return mixed
     */
    public function download(Media $media): void;

    /**
     * @param Media $media
     * @return mixed
     */
    public function delete(Media $media): void;
}