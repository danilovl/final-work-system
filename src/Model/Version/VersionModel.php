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

namespace App\Model\Version;

use App\Entity\Media;
use App\Model\Traits\SimpleInformationTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VersionModel
{
    use SimpleInformationTrait;

    public ?UploadedFile $uploadMedia = null;

    public static function fromMedia(Media $media): self
    {
        $model = new self;
        $model->name = $media->getName();
        $model->description = $media->getDescription();
        $model->uploadMedia = $media->getUploadMedia();

        return $model;
    }
}
