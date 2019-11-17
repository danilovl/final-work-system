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

use FinalWork\FinalWorkBundle\Entity\Task;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use FinalWork\SonataUserBundle\Entity\Group;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class UserGroupFactory extends BaseModelFactory
{
    /**
     * @param UserGroupModel $userGroupModel
     * @param Group $group
     * @return Group
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        UserGroupModel $userGroupModel,
        Group $group = null
    ): Group {
        $group = $group ?? new Group($userGroupModel->name);
        $group = $this->fromModel($group, $userGroupModel);

        $this->em->persist($group);
        $this->em->flush();

        return $group;
    }

    /**
     * @param Group $group
     * @param UserGroupModel $userGroupModel
     * @return Group
     */
    public function fromModel(
        Group $group,
        UserGroupModel $userGroupModel
    ): Group {
        $group->setName($userGroupModel->name);

        return $group;
    }
}
