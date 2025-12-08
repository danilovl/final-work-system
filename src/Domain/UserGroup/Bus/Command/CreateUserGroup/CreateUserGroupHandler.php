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

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Factory\UserGroupFactory;

readonly class CreateUserGroupHandler implements CommandHandlerInterface
{
    public function __construct(private UserGroupFactory $userGroupFactory) {}

    public function __invoke(CreateUserGroupCommand $command): Group
    {
        return $this->userGroupFactory->flushFromModel($command->userGroupModel);
    }
}
