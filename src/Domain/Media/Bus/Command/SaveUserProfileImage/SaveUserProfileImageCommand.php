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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class SaveUserProfileImageCommand implements CommandInterface
{
    private function __construct(
        public User $owner,
        public UploadedFile $uploadMedia,
        public ?Media $profileImage,
        public ?string $name
    ) {}

    public static function create(
        User $owner,
        UploadedFile $uploadMedia,
        ?Media $profileImage,
        ?string $name
    ): self {
        return new self($owner, $uploadMedia, $profileImage, $name);
    }
}
