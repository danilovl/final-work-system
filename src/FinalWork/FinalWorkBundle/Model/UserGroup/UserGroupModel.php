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

namespace FinalWork\FinalWorkBundle\Model\UserGroup;

use FinalWork\SonataUserBundle\Entity\Group;
use Symfony\Component\Validator\Constraints as Assert;

class UserGroupModel
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @param Group $group
     * @return UserGroupModel
     */
    public static function fromGroup(Group $group): self
    {
        $model = new self();
        $model->name = $group->getName();

        return $model;
    }
}
