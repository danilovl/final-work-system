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

namespace App\Domain\Media\Bus\Command\SaveUserProfileImage;

use App\Infrastructure\Exception\RuntimeException;
use App\Infrastructure\Interfaces\Bus\{CommandHandlerInterface, QueryBusInterface};
use App\Domain\MediaType\Bus\Query\MediaType\{GetMediaTypeQuery, GetMediaTypeQueryResult};
use App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeByName\{GetMediaMimeTypeByNameQuery, GetMediaMimeTypeByNameQueryResult};
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\Media\Entity\Media;
use App\Infrastructure\Service\EntityManagerService;

readonly class SaveUserProfileImageHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(SaveUserProfileImageCommand $command): Media
    {
        $uploadMedia = $command->uploadMedia;
        $mimeType = $uploadMedia->getMimeType();
        $getMediaMimeTypeByNameQuery = GetMediaMimeTypeByNameQuery::create($mimeType);
        /** @var GetMediaMimeTypeByNameQueryResult $getMediaMimeTypeByNameQueryResult */
        $getMediaMimeTypeByNameQueryResult = $this->queryBus->handle($getMediaMimeTypeByNameQuery);
        $mediaMimeType = $getMediaMimeTypeByNameQueryResult->mediaMimeType;

        if ($mediaMimeType === null) {
            throw new RuntimeException("FileMimeType don't exist");
        }

        $media = $command->profileImage;
        if ($media === null) {
            $getMediaTypeQuery = GetMediaTypeQuery::create(MediaTypeConstant::USER_PROFILE_IMAGE->value);
            /** @var GetMediaTypeQueryResult $getMediaTypeQueryResult */
            $getMediaTypeQueryResult = $this->queryBus->handle($getMediaTypeQuery);
            $mediaType = $getMediaTypeQueryResult->mediaType;
            /** @var MediaType $mediaType */

            $media = new Media;
            if ($command->name !== null) {
                $media->setName($command->name);
            }

            $media->setType($mediaType);
            $media->setOwner($command->owner);
        }

        $media->setUploadMedia($uploadMedia);
        $this->entityManagerService->persistAndFlush($media);

        return $media;
    }
}
