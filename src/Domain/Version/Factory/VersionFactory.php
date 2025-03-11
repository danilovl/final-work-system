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

namespace App\Domain\Version\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\Model\VersionModel;

class VersionFactory extends BaseModelFactory
{
    public function createFromModel(
        VersionModel $versionModel,
        Media $media = null
    ): Media {
        $media ??= new Media;
        $media = $this->fromModel($media, $versionModel);

        $this->entityManagerService->persistAndFlush($media);

        return $media;
    }

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
