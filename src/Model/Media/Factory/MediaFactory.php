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

namespace App\Model\Media\Factory;

use App\Entity\Media;
use App\Model\BaseModelFactory;
use App\Model\Media\MediaModel;

class MediaFactory extends BaseModelFactory
{
    public function flushFromModel(
        MediaModel $mediaModel,
        Media $media = null
    ): Media {
        $media = $media ?? new Media;
        $media = $this->fromModel($media, $mediaModel);

        $this->entityManagerService->persistAndFlush($media);

        return $media;
    }

    public function fromModel(
        Media $media,
        MediaModel $mediaModel
    ): Media {
        $media->setType($mediaModel->type);
        $media->setName($mediaModel->name);
        $media->setDescription($mediaModel->description);
        $media->setOwner($mediaModel->owner);
        $media->setCategories($mediaModel->categories);
        $media->setWork($mediaModel->work);
        $media->setActive($mediaModel->active);
        $media->setUploadMedia($mediaModel->uploadMedia);

        if ($mediaModel->mimeType) {
            $media->setMediaName($mediaModel->mediaName);
            $media->setMimeType($mediaModel->mimeType);
            $media->setOriginalExtension($mediaModel->originalExtension);
            $media->setOriginalMediaName($mediaModel->originalMediaName);
        }

        return $media;
    }
}
