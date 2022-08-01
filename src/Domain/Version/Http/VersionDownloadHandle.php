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

use App\Application\Service\MediaService;
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VersionDownloadHandle
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function handle(Media $media): BinaryFileResponse
    {
        return $this->mediaService->download($media);
    }
}
