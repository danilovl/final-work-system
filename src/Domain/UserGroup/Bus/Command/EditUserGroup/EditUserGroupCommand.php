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

namespace App\Domain\UserGroup\Bus\Command\EditUserGroup;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Model\UserGroupModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditUserGroupCommand implements CommandInterface
{
    private function __construct(
        public UserGroupModel $userGroupModel,
        public Group $group
    ) {}

    public static function create(UserGroupModel $userGroupModel, Group $group): self
    {
        return new self($userGroupModel, $group);
    }
}
