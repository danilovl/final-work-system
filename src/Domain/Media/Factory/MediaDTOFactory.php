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

namespace App\Domain\Media\Factory;

use App\Domain\Media\DTO\Api\MediaDTO;
use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\DTO\Api\MediaTypeDTO;
use App\Domain\Work\Factory\WorkDetailDTOFactory;

readonly class MediaDTOFactory
{
    public function __construct(private WorkDetailDTOFactory $workDetailDTOFactory) {}

    public function create(Media $media): MediaDTO
    {
        $type = $media->getType();
        $work = $media->getWork();

        return new MediaDTO(
            id: $media->getId(),
            type: new MediaTypeDTO(
                id: $type->getId(),
                name: $type->getName(),
                description: $type->getDescription()
            ),
            name: $media->getName(),
            description: $media->getDescription(),
            mediaName: $media->getMediaName(),
            mediaSize: $media->getMediaSize(),
            originalExtension: $media->getOriginalExtension(),
            owner: $this->workDetailDTOFactory->createUser($media->getOwner()),
            work: $work !== null ? $this->workDetailDTOFactory->create($work) : null
        );
    }
}
