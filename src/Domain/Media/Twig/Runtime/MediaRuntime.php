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

namespace App\Domain\Media\Twig\Runtime;

use App\Infrastructure\Service\S3ClientService;
use App\Domain\Media\Entity\Media;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;

class MediaRuntime
{
    public function __construct(private readonly S3ClientService $s3ClientService) {}

    #[AsTwigFunction('media_is_file_exist')]
    public function isMediaFileExist(Media $media): bool
    {
        return $this->s3ClientService->doesObjectExist(
            $media->getType()->getFolder(),
            $media->getMediaName()
        );
    }
}
