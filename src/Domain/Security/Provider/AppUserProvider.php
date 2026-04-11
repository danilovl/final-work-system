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

namespace App\Domain\Security\Provider;

use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use Override;
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
    #[Override]
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $user->setSalt(null);

        $this->entityManagerService->flush();
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->userFacade->findByUsername($username);

        return $user ?? throw new UserNotFoundException;
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        $user = $this->userFacade->findByUsername($user->getUserIdentifier());

        return $user ?? throw new UserNotFoundException;
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
