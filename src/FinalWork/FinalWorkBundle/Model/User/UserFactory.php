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

namespace FinalWork\FinalWorkBundle\Model\User;

use Exception;
use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use FinalWork\SonataUserBundle\Entity\User;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};

class UserFactory extends BaseModelFactory
{
    /**
     * @param UserModel $userModel
     * @param User $user
     * @return User
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function flushFromModel(
        UserModel $userModel,
        User $user = null
    ): User {
        $user = $user ?? new User;
        $user = $this->fromModel($user, $userModel);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param UserModel $userModel
     * @return User
     * @throws Exception
     */
    public function fromModel(
        User $user,
        UserModel $userModel
    ): User {
        $user->setDegreeBefore($userModel->degreeBefore);
        $user->setFirstname($userModel->firstName);
        $user->setLastname($userModel->lastName);
        $user->setDegreeAfter($userModel->degreeAfter);
        $user->setPhone($userModel->phone);
        $user->setEmail($userModel->email);
        $user->setUsername($userModel->username);
        $user->setRoles($userModel->roles);
        $user->setGroups($userModel->groups);

        return $user;
    }
}
