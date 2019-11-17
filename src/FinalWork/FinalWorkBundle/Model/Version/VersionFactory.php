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

namespace FinalWork\FinalWorkBundle\Model\Version;

use Exception;
use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class VersionFactory extends BaseModelFactory
{
    /**
     * @param VersionModel $versionModel
     * @param Media|null $media
     * @return Media
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function createFromModel(
        VersionModel $versionModel,
        ?Media $media
    ): Media {
        $media = $media ?? new Media;
        $media = $this->fromModel($media, $versionModel);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    /**
     * @param Media $media
     * @param VersionModel $versionModel
     * @return Media
     * @throws Exception
     */
    public function fromModel(
        Media $media,
        VersionModel $versionModel
    ): Media {
        $media->setName($versionModel->name);
        $media->setDescription($versionModel->description);
        $media->setUploadMedia($versionModel->uploadMedia);

        return $media;
    }
}
