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

namespace App\Domain\User\Bus\Command\CreateUser;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\User\Model\UserModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateUserCommand implements CommandInterface
{
    private function __construct(public UserModel $userModel) {}

    public static function create(UserModel $userModel): self
    {
        return new self($userModel);
    }
}
