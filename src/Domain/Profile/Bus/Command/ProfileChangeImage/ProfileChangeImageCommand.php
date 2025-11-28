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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Media\Model\MediaModel;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class ProfileChangeImageCommand implements CommandInterface
{
    private function __construct(
        public MediaModel $mediaModel,
        public User $user
    ) {}

    public static function create(MediaModel $mediaModel, User $user): self
    {
        return new self($mediaModel, $user);
    }
}
