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

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\Media\Bus\Command\SaveUserProfileImage\SaveUserProfileImageCommand;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Bus\Command\ChangeUserProfileImage\ChangeUserProfileImageCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ProfileChangeImageHandler implements CommandHandlerInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(ProfileChangeImageCommand $command): void
    {
        $user = $command->user;
        $profileImage = $user->getProfileImage();

        /** @var UploadedFile $uploadMedia */
        $uploadMedia = $command->mediaModel->uploadMedia;

        $saveProfileImageCommand = SaveUserProfileImageCommand::create(
            owner: $user,
            uploadMedia: $uploadMedia,
            profileImage: $profileImage,
            name: $uploadMedia->getFilename()
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
