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

namespace FinalWork\FinalWorkBundle\Model\Media;

use Exception;
use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class MediaFactory extends BaseModelFactory
{
    /**
     * @param MediaModel $mediaModel
     * @param Media $media
     * @return Media
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        MediaModel $mediaModel,
        Media $media = null
    ): Media {
        $media = $media ?? new Media;
        $media = $this->fromModel($media, $mediaModel);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    /**
     * @param Media $media
     * @param MediaModel $mediaModel
     * @return Media
     * @throws Exception
     */
    public function fromModel(
        Media $media,
        MediaModel $mediaModel
    ): Media {
        $media->setName($mediaModel->name);
        $media->setDescription($mediaModel->description);
        $media->setOwner($mediaModel->owner);
        $media->setType($mediaModel->type);

        if ($mediaModel->mimeType !== null) {
            $media->setMimeType($mediaModel->mimeType);
        }

        $media->setCategories($mediaModel->categories);
        $media->setWork($mediaModel->work);

        if ($mediaModel->mediaName !== null) {
            $media->setMediaName($mediaModel->mediaName);
        }

        if ($mediaModel->originalExtension !== null) {
            $media->setOriginalExtension($mediaModel->originalExtension);
        }

        if ($mediaModel->originalMediaName !== null) {
            $media->setOriginalMediaName($mediaModel->originalMediaName);
        }

        $media->setUploadMedia($mediaModel->uploadMedia);

        return $media;
    }
}
