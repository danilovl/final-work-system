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

namespace App\Model\User;

use App\Services\EntityManagerService;
use App\Helper\{
    HashHelper,
    FunctionHelper
};
use App\Model\BaseModelFactory;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFactory extends BaseModelFactory
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(
        EntityManagerService $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder
    ) {
        parent::__construct($entityManager);

        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function flushFromModel(
        UserModel $userModel,
        User $user = null
    ): User {
        $user = $user ?? new User;
        $user = $this->fromModel($user, $userModel);

        $this->em->persistAndFlush($user);

        return $user;
    }

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
        $user->setEmailCanonical($userModel->emailCanonical);
        $user->setPassword($userModel->password);
        $user->setPlainPassword($userModel->plainPassword);
        $user->setSalt($userModel->salt);
        $user->setSkype($userModel->skype);
        $user->setMessageGreeting($userModel->messageGreeting);
        $user->setMessageSignature($userModel->messageSignature);
        $user->setUsername($userModel->username);
        $user->setUsernameCanonical($userModel->usernameCanonical);
        $user->setRoles($userModel->roles);
        $user->setGroups($userModel->groups);
        $user->setLocale($userModel->locale);
        $user->setEnabled($userModel->enable);
        $user->setEnabledEmailNotification($userModel->enabledEmailNotification);

        return $user;
    }

    public function createNewUser(UserModel $userModel): User
    {
        $newUser = new User;
        $newUser->setSalt(HashHelper::generateUserSalt());

        $userModel->enable = true;
        $userModel->emailCanonical = $userModel->email;
        $userModel->salt = $newUser->getSalt();
        $userModel->usernameCanonical = $userModel->username;
        $userModel->roles = [$userModel->role];
        $userModel->plainPassword = FunctionHelper::randomPassword(8);
        $userModel->password = $this->userPasswordEncoder->encodePassword(
            $newUser,
            $userModel->plainPassword
        );

        return $this->flushFromModel($userModel, $newUser);
    }
}
