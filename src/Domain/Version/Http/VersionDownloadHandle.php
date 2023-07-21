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

namespace App\Domain\Version\Http;

use App\Domain\Media\Entity\Media;
use App\Domain\Media\Service\MediaService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

readonly class VersionDownloadHandle
{
    public function __construct(private MediaService $mediaService) {}

    public function handle(Media $media): BinaryFileResponse
    {
        return $this->mediaService->download($media);
    }
}
