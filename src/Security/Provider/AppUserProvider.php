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

namespace App\Security\Provider;

use App\Model\User\Entity\User;
use App\Model\User\Facade\UserFacade;
use App\Service\EntityManagerService;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface,
    PasswordUpgraderInterface
};

class AppUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private UserFacade $userFacade,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function upgradePassword(User $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->entityManagerService->flush($user);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->userFacade->findOneByUsername($username);

        return $user ?? throw new UserNotFoundException;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->userFacade->findOneByUsername($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}