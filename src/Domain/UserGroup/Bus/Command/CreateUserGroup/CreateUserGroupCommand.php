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

namespace App\Domain\UserGroup\Bus\Command\CreateUserGroup;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateUserGroupCommand implements CommandInterface
{
    private function __construct(public UserGroupModel $userGroupModel) {}

    public static function create(UserGroupModel $userGroupModel): self
    {
        return new self($userGroupModel);
    }
}
