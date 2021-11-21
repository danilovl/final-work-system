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

namespace App\Model\Document\Factory;

use App\Model\Media\Entity\Media;
use App\Model\BaseModelFactory;
use App\Model\Document\DocumentModel;

class DocumentFactory extends BaseModelFactory
{
    public function createFromModel(
        DocumentModel $documentModel,
        Media $media = null
    ): Media {
        $media = $media ?? new Media;
        $media = $this->fromModel($media, $documentModel);

        $this->entityManagerService->persistAndFlush($media);

        return $media;
    }

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
