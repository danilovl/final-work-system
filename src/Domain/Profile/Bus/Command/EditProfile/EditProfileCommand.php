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

namespace App\Domain\Profile\Bus\Command\EditProfile;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Model\UserModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditProfileCommand implements CommandInterface
{
    private function __construct(
        public UserModel $userModel,
        public User $user
    ) {}

    public static function create(UserModel $userModel, User $user): self
    {
        return new self($userModel, $user);
    }
}
