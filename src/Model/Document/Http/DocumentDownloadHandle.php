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

namespace App\Model\Document\Http;

use App\Entity\Media;
use App\Service\MediaService;

class DocumentDownloadHandle
{
    public function __construct(private MediaService $mediaService)
    {
    }

    public function handle(Media $media): void
    {
        $this->mediaService->download($media);
    }
}
