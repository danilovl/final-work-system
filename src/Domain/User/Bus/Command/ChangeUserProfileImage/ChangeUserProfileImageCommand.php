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

namespace App\Domain\User\Bus\Command\ChangeUserProfileImage;

use App\Infrastructure\Interfaces\Bus\CommandInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class ChangeUserProfileImageCommand implements CommandInterface
{
    private function __construct(
        public User $user,
        public Media $profileImage
    ) {}

    public static function create(
        User $user,
        Media $profileImage
    ): self {
        return new self($user, $profileImage);
    }
}
