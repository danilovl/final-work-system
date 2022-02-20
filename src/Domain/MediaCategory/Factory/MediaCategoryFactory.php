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

namespace App\Domain\MediaCategory\Factory;

use App\Domain\BaseModelFactory;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\MediaCategoryModel;

class MediaCategoryFactory extends BaseModelFactory
{
    public function flushFromModel(
        MediaCategoryModel $mediaCategoryModel,
        MediaCategory $mediaCategory = null
    ): MediaCategory {
        $mediaCategory = $mediaCategory ?? new MediaCategory;
        $mediaCategory = $this->fromModel($mediaCategory, $mediaCategoryModel);

        $this->entityManagerService->persistAndFlush($mediaCategory);

        return $mediaCategory;
    }

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