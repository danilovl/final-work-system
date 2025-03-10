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

namespace App\Domain\UserGroup\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\UserGroup\Entity\Group;
use App\Domain\UserGroup\Model\UserGroupModel;

class UserGroupFactory extends BaseModelFactory
{
    public function flushFromModel(
        UserGroupModel $userGroupModel,
        Group $group = null
    ): Group {
        $group ??= new Group;
        $group = $this->fromModel($group, $userGroupModel);

        $this->entityManagerService->persistAndFlush($group);

        return $group;
    }

    public function fromModel(
        Group $group,
        UserGroupModel $userGroupModel
    ): Group {
        $group->setName($userGroupModel->name);

        return $group;
    }
}
