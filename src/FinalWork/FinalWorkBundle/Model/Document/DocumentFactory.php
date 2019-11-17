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

namespace FinalWork\FinalWorkBundle\Model\Document;

use Exception;
use FinalWork\FinalWorkBundle\Entity\Media;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class DocumentFactory extends BaseModelFactory
{
    /**
     * @param DocumentModel $documentModel
     * @param Media|null $media
     * @return Media
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function createFromModel(
        DocumentModel $documentModel,
        ?Media $media
    ): Media {
        $media = $media ?? new Media;
        $media = $this->fromModel($media, $documentModel);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    /**
     * @param Media $media
     * @param DocumentModel $documentModel
     * @return Media
     * @throws Exception
     */
    public function fromModel(
        Media $media,
        DocumentModel $documentModel
    ): Media {
        $media->setName($documentModel->name);
        $media->setDescription($documentModel->description);
        $media->setCategories($documentModel->categories);
        $media->setUploadMedia($documentModel->uploadMedia);

        return $media;
    }
}
