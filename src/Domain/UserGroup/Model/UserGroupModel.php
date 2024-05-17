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

namespace App\Domain\UserGroup\Model;

use App\Domain\UserGroup\Entity\Group;

class UserGroupModel
{
    public string $name;

    public static function fromGroup(Group $group): self
    {
        $model = new self;
        $model->name = $group->getName();

        return $model;
    }
}
