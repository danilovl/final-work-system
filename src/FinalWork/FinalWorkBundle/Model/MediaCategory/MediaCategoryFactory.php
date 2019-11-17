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

namespace FinalWork\FinalWorkBundle\Model\MediaCategory;

use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class MediaCategoryFactory extends BaseModelFactory
{
    /**
     * @param MediaCategoryModel $mediaCategoryModel
     * @param MediaCategory|null $mediaCategory
     * @return MediaCategory
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        MediaCategoryModel $mediaCategoryModel,
        MediaCategory $mediaCategory = null
    ): MediaCategory {
        $mediaCategory = $mediaCategory ?? new MediaCategory;
        $mediaCategory = $this->fromModel($mediaCategory, $mediaCategoryModel);

        $this->em->persist($mediaCategory);
        $this->em->flush();

        return $mediaCategory;
    }

    /**
     * @param MediaCategory $mediaCategory
     * @param MediaCategoryModel $mediaCategoryModel
     * @return MediaCategory
     */
    public function fromModel(
        MediaCategory $mediaCategory,
        MediaCategoryModel $mediaCategoryModel
    ): MediaCategory {
        $mediaCategory->setName($mediaCategoryModel->name);
        $mediaCategory->setDescription($mediaCategoryModel->description);
        $mediaCategory->setOwner($mediaCategoryModel->owner);

        return $mediaCategory;
    }
}
