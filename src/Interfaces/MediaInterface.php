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

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\{
    Work,
    Media
};

interface MediaInterface
{
    public function createMedia(Media $media, int $mediaType, Work $work = null): void;
    public function editMedia(Media $media, Work $work = null): void;
    public function downloadMedia(Media $media): BinaryFileResponse;
    public function deleteMedia(Media $media): void;
}