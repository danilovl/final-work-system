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

namespace App\Domain\Profile\Bus\Command\ProfileCreateImageWebCamera;

use App\Infrastructure\Exception\RuntimeException;
use App\Application\Helper\FileHelper;
use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Application\Service\ResizeImageService;
use App\Domain\Media\Bus\Command\SaveUserProfileImage\SaveUserProfileImageCommand;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Bus\Command\ChangeUserProfileImage\ChangeUserProfileImageCommand;
use App\Domain\MediaMimeType\Constant\MediaMimeTypeTypeConstant;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ProfileCreateImageWebCameraHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ResizeImageService $resizeImageService,
        private ParameterServiceInterface $parameterService
    ) {}

    public function __invoke(ProfileCreateImageWebCameraCommand $command): void
    {
        $user = $command->user;
        $imageData = $command->imageData;
        $profileImage = $user->getProfileImage();

        $maxImageWidth = $this->parameterService
            ->getInt('constraints.profile.image.maxWidth');

        $imageData = $this->resizeImageService->resizeBase64Image($imageData, $maxImageWidth, true);
        if ($imageData === null) {
            throw new RuntimeException('Cannot resize image.');
        }

        $pngExtension = MediaMimeTypeTypeConstant::PNG['extension'];

        $tmpFilePath = FileHelper::createTmpFile(
            $pngExtension,
            base64_decode($imageData)
        );

        $uploadMedia = new UploadedFile(
            $tmpFilePath,
            sprintf('%s-%s.%s', 'web-camera-profile-image', uniqid(), $pngExtension),
            MediaMimeTypeTypeConstant::PNG['mimeType'],
            null,
            true
        );

        $saveProfileImageCommand = SaveUserProfileImageCommand::create(
            owner: $user,
            uploadMedia: $uploadMedia,
            profileImage: $profileImage,
            name: null
        );

        /** @var Media $media */
        $media = $this->commandBus->dispatchResult($saveProfileImageCommand);

        if ($profileImage === null) {
            $changeProfileImageCommand = ChangeUserProfileImageCommand::create(
                user: $user,
                profileImage: $media
            );

            $this->commandBus->dispatch($changeProfileImageCommand);
        }
    }
}
