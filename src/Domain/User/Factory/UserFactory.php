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

namespace App\Domain\User\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Application\Helper\{
    FunctionHelper
};
use App\Application\Helper\HashHelper;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Model\UserModel;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory extends BaseModelFactory
{
    public function __construct(
        EntityManagerService $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
        parent::__construct($entityManager);
    }

    public function flushFromModel(
        UserModel $userModel,
        User $user = null
    ): User {
        $user ??= new User;
        $user = $this->fromModel($user, $userModel);

        $this->entityManagerService->persistAndFlush($user);

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
        $userModel->password = $this->userPasswordHasher->hashPassword(
            $newUser,
            $userModel->plainPassword
        );

        return $this->flushFromModel($userModel, $newUser);
    }
}
