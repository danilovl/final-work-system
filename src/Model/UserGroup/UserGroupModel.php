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

namespace App\Model\UserGroup;

use App\Model\UserGroup\Entity\Group;

class UserGroupModel
{
    public ?string $name = null;

    public static function fromGroup(Group $group): self
    {
        $model = new self;
        $model->name = $group->getName();

        return $model;
    }
}
