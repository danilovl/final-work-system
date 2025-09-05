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

namespace App\Domain\Work\Bus\Command\EditAuthor;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Model\UserModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditAuthorCommand implements CommandInterface
{
    private function __construct(public User $user, public UserModel $userModel) {}

    public static function create(User $user, UserModel $userModel): self
    {
        return new self($user, $userModel);
    }
}
