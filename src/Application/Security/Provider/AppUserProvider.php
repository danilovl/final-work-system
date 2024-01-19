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

namespace App\Application\Security\Provider;

use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\{
    UserInterface,
    UserProviderInterface,
    PasswordUpgraderInterface,
    PasswordAuthenticatedUserInterface
};

class AppUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly EntityManagerService $entityManagerService
    ) {}

    /**
     * @param User $user
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->entityManagerService->flush();
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
        $user = $this->userFacade->findOneByUsername($user->getUserIdentifier());

        return $user ?? throw new UserNotFoundException;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
