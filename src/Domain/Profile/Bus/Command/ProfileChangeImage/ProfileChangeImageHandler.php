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

namespace App\Domain\Profile\Bus\Command\ProfileChangeImage;

use App\Application\Exception\RuntimeException;
use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Facade\MediaTypeFacade;
use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaType\Constant\MediaTypeConstant;
use App\Domain\MediaType\Entity\MediaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ProfileChangeImageHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private MediaTypeFacade $mediaTypeFacade
    ) {}

    public function __invoke(ProfileChangeImageCommand $command): void
    {
        $mediaModel = $command->mediaModel;
        $user = $command->user;
        $profileImage = $user->getProfileImage();

        /** @var UploadedFile $uploadMedia */
        $uploadMedia = $mediaModel->uploadMedia;
        $mimeType = $uploadMedia->getMimeType();

        $mediaMimeType = $this->entityManagerService
            ->getRepository(MediaMimeType::class)
            ->findOneBy(['name' => $mimeType]);

        if ($mediaMimeType === null) {
            throw new RuntimeException("FileMimeType don't exist");
        }

        $media = $profileImage ?? new Media;
        if ($profileImage === null) {
            /** @var MediaType $mediaType */
            $mediaType = $this->mediaTypeFacade->findById(MediaTypeConstant::USER_PROFILE_IMAGE->value);

            $media = new Media;
            $media->setName($uploadMedia->getFilename());
            $media->setType($mediaType);
            $media->setOwner($user);
        }

        $media->setUploadMedia($uploadMedia);
        $this->entityManagerService->persistAndFlush($media);

        if ($profileImage === null) {
            $user->setProfileImage($media);
            $this->entityManagerService->flush();
        }
    }
}
